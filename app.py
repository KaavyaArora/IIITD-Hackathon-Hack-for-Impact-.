from flask import Flask, jsonify
from flask_cors import CORS
import mysql.connector

app = Flask(__name__)
CORS(app)

@app.route("/articles", methods=["GET"])
def get_articles():
    try:
        # Connect to MySQL Database
        db_connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",  # Empty password
            database="news_db"
        )

        cursor = db_connection.cursor()
        cursor.execute("SELECT * FROM articles")
        articles = cursor.fetchall()

        print(articles)  # For debugging

        return jsonify(articles)

    except Exception as e:
        return jsonify({"error": str(e)})


if __name__ == "__main__":
    app.run(debug=False)
