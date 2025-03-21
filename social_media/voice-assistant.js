// Voice Assistant JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const micButton = document.getElementById('mic-button');
    const voicePanel = document.getElementById('voice-panel');
    const closePanel = document.getElementById('close-panel');
    const statusIndicator = document.getElementById('voice-status-indicator');
    const statusText = document.getElementById('voice-status-text');
    const commandList = document.getElementById('command-list');
    const voiceToast = document.getElementById('voice-toast');
    
    // Speech recognition setup
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition;
    
    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.lang = 'en-US';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        
        // Define commands based on current page
        const currentPage = getCurrentPage();
        populateCommandList(currentPage);
        
        // Mic button click handler
        micButton.addEventListener('click', function() {
            if (voicePanel.classList.contains('active')) {
                toggleVoiceRecognition();
            } else {
                voicePanel.classList.add('active');
            }
        });
        
        // Close panel button handler
        closePanel.addEventListener('click', function() {
            voicePanel.classList.remove('active');
            stopVoiceRecognition();
        });
        
        // Recognition events
        recognition.onstart = function() {
            statusIndicator.classList.add('listening');
            statusText.textContent = 'Listening...';
            micButton.classList.add('listening');
        };
        
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript.toLowerCase().trim();
            
            statusIndicator.classList.remove('listening');
            statusIndicator.classList.add('processing');
            statusText.textContent = `Processing: "${transcript}"`;
            
            setTimeout(() => {
                processVoiceCommand(transcript, currentPage);
            }, 500);
        };
        
        recognition.onerror = function(event) {
            if (event.error !== 'no-speech') {
                showToast(`Error: ${event.error}`);
            }
            resetVoiceStatus();
        };
        
        recognition.onend = function() {
            resetVoiceStatus();
        };
    } else {
        micButton.style.display = 'none';
        showToast('Speech recognition not supported in this browser');
    }
    
    // Helper functions
    function getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('index') || path.endsWith('/')) return 'login';
        if (path.includes('register')) return 'register';
        if (path.includes('home')) return 'timeline';
        return 'unknown';
    }
    
    function populateCommandList(page) {
        // Clear existing commands
        commandList.innerHTML = '';
        
        // Common commands for all pages
        const commonCommands = [
            '"Scroll down" - Scroll down the page',
            '"Scroll up" - Scroll up the page',
            '"Go back" - Return to previous page',
            '"Refresh page" - Reload the current page'
        ];
        
        // Page-specific commands
        let pageCommands = [];
        
        switch (page) {
            case 'login':
                pageCommands = [
                    '"Login" - Submit login form',
                    '"Register" - Go to registration page',
                    '"Enter username [name]" - Fill in username field',
                    '"Enter password [pass]" - Fill in password field',
                    '"Remember me" - Toggle remember me checkbox'
                ];
                break;
                
            case 'register':
                pageCommands = [
                    '"Register now" - Submit registration form',
                    '"Go to login" - Return to login page',
                    '"Enter username [name]" - Fill in username field',
                    '"Enter email [address]" - Fill in email field',
                    '"Enter password [pass]" - Fill in password field'
                ];
                break;
                
            case 'timeline':
                pageCommands = [
                    '"Go to profile" - Navigate to your profile',
                    '"Go to followers" - View followers page',
                    '"Logout" - Sign out',
                    '"Post tweet" - Focus on tweet input',
                    '"Tweet [your message]" - Write and post a tweet',
                    '"Show comments" - View comments on selected tweet',
                    '"Like tweet" - Like the current tweet',
                    '"Follow suggestions" - View suggested users'
                ];
                break;
        }
        
        // Add all commands to list
        const allCommands = [...pageCommands, ...commonCommands];
        allCommands.forEach(command => {
            const li = document.createElement('li');
            li.textContent = command;
            commandList.appendChild(li);
        });
    }
    
    function toggleVoiceRecognition() {
        if (statusIndicator.classList.contains('listening')) {
            stopVoiceRecognition();
        } else {
            startVoiceRecognition();
        }
    }
    
    function startVoiceRecognition() {
        try {
            recognition.start();
        } catch (error) {
            console.error('Recognition already started:', error);
        }
    }
    
    function stopVoiceRecognition() {
        try {
            recognition.stop();
        } catch (error) {
            console.error('Recognition already stopped:', error);
        }
    }
    
    function resetVoiceStatus() {
        statusIndicator.classList.remove('listening', 'processing', 'success');
        statusText.textContent = 'Click the mic to start';
        micButton.classList.remove('listening');
    }
    
    function processVoiceCommand(command, page) {
        console.log('Processing command:', command);
        
        // Common commands
        if (command.includes('scroll down')) {
            window.scrollBy(0, 300);
            commandSuccessful('Scrolling down');
            return;
        }
        
        if (command.includes('scroll up')) {
            window.scrollBy(0, -300);
            commandSuccessful('Scrolling up');
            return;
        }
        
        if (command.includes('go back')) {
            window.history.back();
            commandSuccessful('Going back');
            return;
        }
        
        if (command.includes('refresh page') || command.includes('reload page')) {
            window.location.reload();
            return;
        }
        
        // Page-specific commands
        switch (page) {
            case 'login':
                processLoginCommands(command);
                break;
                
            case 'register':
                processRegisterCommands(command);
                break;
                
            case 'timeline':
                processTimelineCommands(command);
                break;
        }
    }
    
    function processLoginCommands(command) {
        if (command === 'login') {
            document.querySelector('form').submit();
            commandSuccessful('Logging in');
            return;
        }
        
        if (command === 'register' || command.includes('go to register')) {
            window.location.href = 'register.html';
            commandSuccessful('Going to register');
            return;
        }
        
        if (command.includes('enter username')) {
            const username = command.replace('enter username', '').trim();
            document.getElementById('username').value = username;
            commandSuccessful('Username entered');
            return;
        }
        
        if (command.includes('enter password')) {
            const password = command.replace('enter password', '').trim();
            document.getElementById('password').value = password;
            commandSuccessful('Password entered');
            return;
        }
        
        if (command.includes('remember me')) {
            const checkbox = document.getElementById('remember');
            checkbox.checked = !checkbox.checked;
            commandSuccessful(checkbox.checked ? 'Remember me checked' : 'Remember me unchecked');
            return;
        }
        
        commandNotFound(command);
    }
    
    function processRegisterCommands(command) {
        if (command === 'register now' || command === 'sign up') {
            document.querySelector('form').submit();
            commandSuccessful('Registering');
            return;
        }
        
        if (command.includes('go to login')) {
            window.location.href = 'index.html';
            commandSuccessful('Going to login');
            return;
        }
        
        if (command.includes('enter username')) {
            const username = command.replace('enter username', '').trim();
            document.getElementById('username').value = username;
            commandSuccessful('Username entered');
            return;
        }
        
        if (command.includes('enter email')) {
            const email = command.replace('enter email', '').trim();
            document.getElementById('email').value = email;
            commandSuccessful('Email entered');
            return;
        }
        
        if (command.includes('enter password')) {
            const password = command.replace('enter password', '').trim();
            document.getElementById('password').value = password;
            commandSuccessful('Password entered');
            return;
        }
        
        commandNotFound(command);
    }
    
    function processTimelineCommands(command) {
        if (command.includes('go to profile')) {
            const profileLink = document.querySelector('a[href*="profile.php"]');
            if (profileLink) profileLink.click();
            commandSuccessful('Going to profile');
            return;
        }
        
        if (command.includes('go to followers')) {
            const followersLink = document.querySelector('a[href*="followers.php"]');
            if (followersLink) followersLink.click();
            commandSuccessful('Going to followers');
            return;
        }
        
        if (command === 'logout') {
            const logoutLink = document.querySelector('a[href*="logout.php"]');
            if (logoutLink) logoutLink.click();
            commandSuccessful('Logging out');
            return;
        }
        
        if (command === 'post tweet') {
            document.querySelector('textarea[name="tweet_content"]').focus();
            commandSuccessful('Ready to tweet');
            return;
        }
        
        if (command.startsWith('tweet ')) {
            const tweetContent = command.replace('tweet', '').trim();
            const textarea = document.querySelector('textarea[name="tweet_content"]');
            textarea.value = tweetContent;
            textarea.focus();
            
            // Submit after a short delay
            setTimeout(() => {
                document.getElementById('tweet-form').submit();
            }, 500);
            
            commandSuccessful('Posting tweet');
            return;
        }
        
        if (command.includes('show comments')) {
            // Click on the first tweet's comment button if none are selected
            const commentBtn = document.querySelector('.comment-btn');
            if (commentBtn) commentBtn.click();
            commandSuccessful('Showing comments');
            return;
        }
        
        if (command.includes('like tweet')) {
            // Click on the first tweet's like button if none are selected
            const likeBtn = document.querySelector('.like-btn');
            if (likeBtn) likeBtn.click();
            commandSuccessful('Liking tweet');
            return;
        }
        
        if (command.includes('follow suggestions')) {
            document.querySelector('.who-to-follow').scrollIntoView({
                behavior: 'smooth'
            });
            commandSuccessful('Showing follow suggestions');
            return;
        }
        
        commandNotFound(command);
    }
    
    function commandSuccessful(message) {
        statusIndicator.classList.remove('processing');
        statusIndicator.classList.add('success');
        statusText.textContent = 'Command recognized';
        showToast(message);
        
        // Reset after a delay
        setTimeout(resetVoiceStatus, 2000);
    }
    
    function commandNotFound(command) {
        showToast(`Command not recognized: "${command}"`);
        resetVoiceStatus();
    }
    
    function showToast(message) {
        voiceToast.textContent = message;
        voiceToast.classList.add('active');
        
        setTimeout(() => {
            voiceToast.classList.remove('active');
        }, 3000);
    }
});