from flask import Flask, jsonify, send_from_directory
from flask_cors import CORS
import mysql.connector
import os

# Optional: Load environment variables from a .env file in local development
from dotenv import load_dotenv
load_dotenv()

# Tell Flask to serve static files from "static/"
app = Flask(__name__, static_folder="static")
CORS(app)

@app.route("/articles", methods=["GET"])
def get_articles():
    try:
        db_connection = mysql.connector.connect(
            host=os.environ.get("DB_HOST"),
            user=os.environ.get("DB_USER"),
            password=os.environ.get("DB_PASS"),
            database=os.environ.get("DB_NAME")
        )
        cursor = db_connection.cursor()
        cursor.execute("SELECT * FROM articles")
        articles = cursor.fetchall()

        return jsonify(articles)

    except Exception as e:
        return jsonify({"error": str(e)})

# ✅ Serve React frontend
@app.route("/")
def serve_index():
    return send_from_directory(app.static_folder, "index.html")

@app.route("/<path:path>")
def serve_static(path):
    file_path = os.path.join(app.static_folder, path)
    if os.path.exists(file_path):
        return send_from_directory(app.static_folder, path)
    else:
        return send_from_directory(app.static_folder, "index.html")

# ✅ Run Flask server
if __name__ == "__main__":
    app.run(debug=True)
