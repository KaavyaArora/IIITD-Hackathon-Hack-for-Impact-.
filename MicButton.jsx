import React, { useState, useEffect } from "react";

const MicButton = ({ navigate }) => {
  const [listening, setListening] = useState(false);
  const [error, setError] = useState(null);

  const handleAction = (action) => {
    if (action.startsWith("http")) {
      window.location.href = action;
    } else if (action === "scroll_down") {
      window.scrollBy(0, 500);
    } else if (action === "scroll_up") {
      window.scrollBy(0, -500);
    } else if (action === "go_back") {
      window.history.back();
    } else {
      navigate(action);
    }
  };

  useEffect(() => {
    let resultCheckInterval;
    
    if (listening) {
      // Start checking for results
      resultCheckInterval = setInterval(async () => {
        try {
          const response = await fetch('http://localhost:5000/api/result');
          const data = await response.json();
          
          if (data.status === "still_listening") {
            // Still listening, keep waiting
            return;
          }
          
          // We got a result, clear interval
          clearInterval(resultCheckInterval);
          setListening(false);
          
          if (data.success) {
            console.log("📝 Recognized:", data.text);
            if (data.action) {
              handleAction(data.action);
            }
          } else if (data.error) {
            setError(data.error);
            setTimeout(() => setError(null), 3000);
          }
        } catch (err) {
          console.error("Error checking result:", err);
          setError("Communication error");
          setListening(false);
          clearInterval(resultCheckInterval);
        }
      }, 500);
    }
    
    return () => {
      if (resultCheckInterval) {
        clearInterval(resultCheckInterval);
      }
    };
  }, [listening, navigate]);

  const startListening = async () => {
    try {
      setError(null);
      const response = await fetch('http://localhost:5000/api/listen', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      });
      
      const data = await response.json();
      
      if (data.success) {
        setListening(true);
      } else {
        setError(data.error || "Unknown error");
        setTimeout(() => setError(null), 3000);
      }
    } catch (err) {
      console.error("Error starting listening:", err);
      setError("Server communication error");
      setTimeout(() => setError(null), 3000);
    }
  };

  return (
    <div className="flex flex-col items-center">
      <button 
        onClick={startListening} 
        className={`p-3 ${listening ? 'bg-red-500' : 'bg-blue-500'} text-white rounded-full text-lg shadow-md`}
        disabled={listening}
      >
        🎤 {listening ? "Listening..." : "Click to Speak"}
      </button>
      
      {error && (
        <div className="mt-2 text-red-500 text-sm">
          ❌ {error}
        </div>
      )}
    </div>
  );
};

export default MicButton;