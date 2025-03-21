import speech_recognition as sr

recognizer = sr.Recognizer()
mic = sr.Microphone()  

with mic as source:
    print("🎤 Speak something...")
    recognizer.adjust_for_ambient_noise(source)
    audio = recognizer.listen(source)

print("🔄 Playing back your recorded audio...")
with open("test.wav", "wb") as f:
    f.write(audio.get_wav_data())