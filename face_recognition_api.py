import os
import cv2
import numpy as np
import base64
from flask import Flask, request, jsonify
from flask_cors import CORS
from deepface import DeepFace
import mysql.connector
import json
import uuid
from datetime import datetime

app = Flask(__name__)
CORS(app, resources={r"/verify": {"origins": "*"}})

def get_db_connection():
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='face att'
    )
    cursor = conn.cursor(dictionary=True)
    return conn, cursor

# Directory to temporarily save uploaded images
UPLOAD_FOLDER = 'temp_uploads'
if not os.path.exists(UPLOAD_FOLDER):
    os.makedirs(UPLOAD_FOLDER)

# Base directory where your labels folder is located
BASE_DIR = os.path.abspath("C:/xampp/htdocs/PBL PROJECT/face attendance/")

@app.route('/verify', methods=['POST'])
def verify_face():
    print("✅ Request received at /verify")
    message = "Processing...!!"
    face_match_success = False
    conn, cursor = get_db_connection()  # Get connection at the start

    try:
        data = request.json
        current_datetime = datetime.now()
        current_date = current_datetime.strftime('%Y-%m-%d')
        current_time = current_datetime.strftime('%H:%M:%S')
        registration_number = data.get('registrationNumber')
        current_course_code = data.get('currentCourseCode')
        subject_name = data.get('subjectName')
        uploaded_image_data = data.get('image')

        print(f"🆔 Registration Number: {registration_number}")

        if registration_number == "testerror":
            return jsonify({'status': 'error', 'message': 'Simulated error for testing.'}), 500

        target_folder = os.path.join(BASE_DIR, 'resources', 'labels', registration_number)
        print(f"📂 Target Folder Path: {target_folder}")

        if not os.path.exists(target_folder):
            print("❌ No folder found for registration number.")
            message = "❌ Contact your admin/teacher"
            return jsonify({'error': f'No folder found for registration number: {registration_number}'}), 404

        stored_image_paths = [
            os.path.join(target_folder, img) for img in os.listdir(target_folder)
            if img.lower().endswith(('.png', '.jpg', '.jpeg'))
        ]
        print(f"🖼️ Stored Images Found: {stored_image_paths}")

        if not stored_image_paths:
            return jsonify({'error': 'No valid images found in the registration number folder.'}), 404

        # Decode uploaded image
        uploaded_image = cv2.imdecode(
            np.frombuffer(base64.b64decode(uploaded_image_data), np.uint8),
            cv2.IMREAD_COLOR
        )

        if uploaded_image is None:
            print("❌ Failed to decode the uploaded image.")
            message = "❌ Failed to decode the uploaded image"
            return jsonify({'error': 'Failed to decode the uploaded image.'}), 400

        temp_image_path = os.path.join(UPLOAD_FOLDER, f"{str(uuid.uuid4())}.jpg")
        cv2.imwrite(temp_image_path, uploaded_image)
        print(f"✅ Uploaded Image Saved at: {temp_image_path}")

        for stored_image_path in stored_image_paths:
            try:
                print(f"🔍 Comparing with: {stored_image_path}")
                result = DeepFace.verify(
                    img1_path=temp_image_path,
                    img2_path=stored_image_path,
                    model_name='Facenet',
                    distance_metric='cosine',
                    enforce_detection=True
                )

                if result.get('verified'):
                    os.remove(temp_image_path)
                    face_match_success = True
                    print("✅ Face Matched Successfully!")
                    break
            except Exception as e:
                print(f"❌ Error during face verification: {e}")
                message = "❌ Error during face verification:"
                continue

        if not face_match_success:
            if os.path.exists(temp_image_path):
                os.remove(temp_image_path)
            message = "❌ Face did not match any stored images. Please retry !!"
            print(message)
            return jsonify({'status': 'error', 'message': message}), 401

        # ✅ Check for active session
        session_query = """
            SELECT session_id FROM active_sub
            WHERE subject_name = %s AND NOW() BETWEEN start_time AND end_time
            ORDER BY start_time DESC LIMIT 1
        """
        cursor.execute(session_query, (subject_name,))
        session_result = cursor.fetchone()

        if not session_result:
            message = "❌ No active attendance session found for this subject. Kindly Contact your teacher"
            print(message)
            return jsonify({'status': 'error', 'message': message}), 400

        session_id = session_result['session_id']

        # ✅ Check if already marked
        already_marked_query = """
            SELECT id FROM attendance
            WHERE registrationNumber = %s AND session_id = %s
        """
        cursor.execute(already_marked_query, (registration_number, session_id))
        already_marked = cursor.fetchone()

        if already_marked:
            message = "⚠️ Attendance already marked for this session."
            print(message)
            return jsonify({'status': 'error', 'message': message}), 409

        # ✅ Insert attendance
        insert_query = """
            INSERT INTO attendance (registrationNumber, courseCode, date, time, status, subjectname, session_id)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(insert_query, (
            registration_number, current_course_code, current_date,
            current_time, 'Present', subject_name, session_id
        ))
        conn.commit()

        message = "✅ Face matched. Attendance marked successfully."
        print(message)
        return jsonify({'status': 'success', 'message': message}), 200

    except Exception as e:
        print(f"❌ Internal Server Error: {e}")
        return jsonify({'status': 'error', 'message': str(e)}), 500

    finally:
        cursor.close()
        conn.close()

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
