import React, { useEffect, useState } from "react";
import "./App.css";

function App() {
  const [articles, setArticles] = useState([]);

  useEffect(() => {
    fetch("http://127.0.0.1:5000/articles")
      .then((response) => response.json())
      .then((data) => setArticles(data))
      .catch((error) => console.error("Error fetching articles:", error));
  }, []);

  return (
    <div className="App">
      <h1>Articles</h1>
      <ul>
        {articles.map((article, index) => (
          <li key={index}>
            <h3>{article[1]}</h3> {/* Assuming article[1] is the title */}
            <p>{article[2]}</p>  {/* Assuming article[2] is the content */}
            <small>{article[5]}</small> {/* Assuming article[5] is the published date */}
          </li>
        ))}
      </ul>
    </div>
  );
}

export default App;
