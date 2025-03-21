import speech_recognition as sr
import webbrowser
import pyautogui

# Define commands and their actions
commands = {
    "go to articles": "http://localhost:3000/",
    "open expert advice": "http://yourwebsite.com/expert-advice",
    "scroll down": "scroll_down",
    "scroll up": "scroll_up",
    "go back": "go_back"
}

def recognize_speech():
    recognizer = sr.Recognizer()
    with sr.Microphone() as source:
        print("🎤 Speak a command...")
        recognizer.adjust_for_ambient_noise(source)
        audio = recognizer.listen(source)

    try:
        text = recognizer.recognize_google(audio).lower()
        print("📝 You said:", text)
        
        # Perform action based on the recognized command
        if text in commands:
            action = commands[text]
            if action.startswith("http"):
                print(f"🌐 Opening {action}...")
                webbrowser.open(action)  # Opens the webpage
            elif action == "scroll_down":
                pyautogui.scroll(-500)  # Scroll down
            elif action == "scroll_up":
                pyautogui.scroll(500)  # Scroll up
            elif action == "go_back":
                pyautogui.hotkey("alt", "left")  # Go back in browser history
        else:
            print("❌ Command not recognized!")

    except sr.UnknownValueError:
        print("❌ Couldn't understand the audio.")
    except sr.RequestError:
        print("🚨 API unavailable or quota exceeded.")

# Keep running the assistant to listen continuously
while True:
    recognize_speech()
