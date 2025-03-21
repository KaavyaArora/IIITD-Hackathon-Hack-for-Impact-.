import mysql.connector
import requests
import time

# Database Configuration
DB_CONFIG = {
    "host": "localhost",
    "user": "root",  # Change if needed
    "password": "",  # Add your MySQL password if required
    "database": "news_db"
}

# News API (Replace with your actual API Key)
NEWS_API_KEY = "c5912e7953a447f9b802fa343d54f408"
CATEGORIES = ["business", "politics", "entertainment", "technology"]

# Function to fetch news for a category
def fetch_news(category):
    url = f"https://newsapi.org/v2/top-headlines?category={category}&country=us&apiKey={NEWS_API_KEY}"
    try:
        response = requests.get(url)
        data = response.json()
        
        if "articles" in data:
            print(f"Found {len(data['articles'])} articles for {category}")
            return data["articles"]
        else:
            print(f"API response for {category}: {data}")
            return []
    except Exception as e:
        print(f"Error fetching news for {category}: {str(e)}")
        return []

# Function to check if an article already exists
def article_exists(title, cursor):
    query = "SELECT COUNT(*) FROM articles WHERE title = %s"
    cursor.execute(query, (title,))
    return cursor.fetchone()[0] > 0  # Returns True if article exists

# Function to insert news into MySQL (Avoiding Duplicates)
def insert_news_into_db(category, articles):
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        for article in articles:
            title = article["title"]
            content = article["description"] or "No description"
            image_url = article["urlToImage"] or ""
            published_at = article["publishedAt"]
            
            # Check for duplicate article
            if not article_exists(title, cursor):
                query = """
                    INSERT INTO articles (title, content, category, image_url, published_at) 
                    VALUES (%s, %s, %s, %s, %s)
                """
                cursor.execute(query, (title, content, category, image_url, published_at))
                conn.commit()
                print(f"Inserted: {title}")
            else:
                print(f"Skipped (Duplicate): {title}")

        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error inserting news: {str(e)}")

# Run periodically to fetch & insert news
def update_news_periodically():
    while True:
        for category in CATEGORIES:
            print(f"\nFetching news for: {category}")
            articles = fetch_news(category)
            insert_news_into_db(category, articles)
        
        print("\n✅ News update complete. Waiting for next update...\n")
        time.sleep(3600)  # Wait 1 hour before the next update

# Run the script
if __name__ == "__main__":
    update_news_periodically()