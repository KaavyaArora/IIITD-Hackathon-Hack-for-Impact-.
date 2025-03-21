// Add this to your script.js file

document.addEventListener('DOMContentLoaded', function() {
    const micButton = document.getElementById('mic-button');
    const voicePanel = document.getElementById('voice-panel');
    const closePanel = document.getElementById('close-panel');
    const voiceStatusIndicator = document.getElementById('voice-status-indicator');
    const voiceStatusText = document.getElementById('voice-status-text');
    
    let isListening = false;
    let recognition;
    
    // Check if browser supports SpeechRecognition
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        
        // Define commands and their actions
        const commands = {
            "go to home": () => window.location.href = "index.html",
            "go to articles": () => window.location.href = "http://localhost:3001/",
            "seek advice": () => window.location.href = "seek-advice.html",
            "open chat": () => window.location.href = "social_media/index.html",
            "scroll down": () => window.scrollBy(0, 500),
            "scroll up": () => window.scrollBy(0, -500),
            "go back": () => window.history.back(),
            "register as advisor": () => window.location.href = "register.html",
        };
        
        // Set up recognition events
        recognition.onstart = function() {
            isListening = true;
            micButton.classList.add('listening');
            voiceStatusIndicator.classList.add('listening');
            voiceStatusText.textContent = "Listening...";
        };
        
        recognition.onresult = function(event) {
            isListening = false;
            micButton.classList.remove('listening');
            voiceStatusIndicator.classList.remove('listening');
            voiceStatusIndicator.classList.add('processing');
            voiceStatusText.textContent = "Processing...";
            
            const transcript = event.results[0][0].transcript.toLowerCase().trim();
            
            // Show what was recognized
            voiceStatusText.textContent = `"${transcript}"`;
            
            // Check if transcript matches a command
            setTimeout(() => {
                let commandFound = false;
                
                for (const [command, action] of Object.entries(commands)) {
                    if (transcript.includes(command)) {
                        // Show success
                        voiceStatusIndicator.classList.remove('processing');
                        voiceStatusIndicator.classList.add('success');
                        voiceStatusText.textContent = `Executing: "${command}"`;
                        
                        // Create and show toast notification
                        showToast(`Executing: ${command}`);
                        
                        // Execute the command after a short delay
                        setTimeout(() => {
                            action();
                        }, 500);
                        
                        commandFound = true;
                        break;
                    }
                }
                
                if (!commandFound) {
                    voiceStatusIndicator.classList.remove('processing');
                    voiceStatusText.textContent = "Command not recognized. Try again.";
                    setTimeout(() => {
                        voiceStatusText.textContent = "Click the mic to start";
                        voiceStatusIndicator.classList.remove('success');
                    }, 3000);
                }
            }, 500);
        };
        
        recognition.onerror = function(event) {
            isListening = false;
            micButton.classList.remove('listening');
            voiceStatusIndicator.classList.remove('listening');
            voiceStatusText.textContent = "Error occurred. Try again.";
            console.error('Speech recognition error:', event.error);
        };
        
        recognition.onend = function() {
            isListening = false;
            micButton.classList.remove('listening');
            if (!voiceStatusIndicator.classList.contains('success') && !voiceStatusIndicator.classList.contains('processing')) {
                voiceStatusIndicator.classList.remove('listening');
                voiceStatusText.textContent = "Click the mic to start";
            }
        };
    } else {
        // Browser doesn't support speech recognition
        micButton.addEventListener('click', function() {
            showToast("Your browser doesn't support voice recognition");
        });
    }
    
    // Toggle voice panel
    micButton.addEventListener('click', function() {
        voicePanel.classList.toggle('active');
        
        if (voicePanel.classList.contains('active') && !isListening && recognition) {
            // Start listening after a short delay to let the animation complete
            setTimeout(() => {
                try {
                    recognition.start();
                } catch (e) {
                    console.error('Recognition error:', e);
                }
            }, 300);
        } else if (isListening && recognition) {
            recognition.stop();
        }
    });
    
    // Close panel button
    closePanel.addEventListener('click', function() {
        voicePanel.classList.remove('active');
        if (isListening && recognition) {
            recognition.stop();
        }
    });
    
    // Function to show toast notifications
    function showToast(message) {
        // Remove any existing toast
        const existingToast = document.querySelector('.voice-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create new toast
        const toast = document.createElement('div');
        toast.className = 'voice-toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Show toast with animation
        setTimeout(() => {
            toast.classList.add('active');
        }, 10);
        
        // Remove toast after animation completes
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});