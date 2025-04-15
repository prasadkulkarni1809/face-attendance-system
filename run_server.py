from face_recognition_api import app  # Importing your Flask app from face_recognition_api.py

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000, debug=True)
