document.addEventListener('DOMContentLoaded', function() {
    // Target the container where we'll display experts
    const expertsGrid = document.querySelector('.experts-grid');

    // Fetch registered advisors from the database
    fetch('fetch_advisors.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        // Parse the raw text first to debug any JSON issues
        return response.text().then(text => {
            try {
                // Parse the text to JSON
                return JSON.parse(text);
            } catch (error) {
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(advisors => {
        // Clear existing content including loading indicator
        expertsGrid.innerHTML = '';

        // Check if we got any advisors
        if (!Array.isArray(advisors) || advisors.length === 0) {
            expertsGrid.innerHTML = 'No experts found. Be the first to register!';
            return;
        }

        // Loop through advisors and create cards for each
        advisors.forEach((advisor, index) => {
            // Create expert card HTML
            const expertCard = document.createElement('div');
            expertCard.className = 'expert-card';

            // Calculate rating based on experience (just for demo purposes)
            const rating = Math.min(5, 3 + (parseInt(advisor.experience) / 10));
            const ratingDisplay = rating.toFixed(1);

            // Split expertise into tags
            let expertiseTags = ['Consultant'];
            if (advisor.expertise && typeof advisor.expertise === 'string') {
                expertiseTags = advisor.expertise.split(',').map(tag => tag.trim());
            }
            const tagsHTML = expertiseTags.slice(0, 3).map(tag => `${tag}`).join('');

            // Process hourly rate
            let hourlyRate = 0;
            if (advisor.hourly_rate !== null && advisor.hourly_rate !== undefined) {
                if (typeof advisor.hourly_rate === 'number') {
                    hourlyRate = advisor.hourly_rate;
                } else if (typeof advisor.hourly_rate === 'string') {
                    // Remove any non-numeric chars except decimal point
                    const cleaned = advisor.hourly_rate.replace(/[^\d.]/g, '');
                    hourlyRate = parseFloat(cleaned);
                } else {
                    // Try direct conversion
                    hourlyRate = Number(advisor.hourly_rate);
                }
            }
            // If all else fails, use a default value
            if (isNaN(hourlyRate)) {
                hourlyRate = 99.99; // Default value for display
            }
            // Format for display with 2 decimal places
            const formattedRate = hourlyRate.toFixed(2);

            // Create the card content
            expertCard.innerHTML = `
                <div class="expert-image-container">
                    <img src="${advisor.profile_pic}" alt="${advisor.name}" class="expert-image">
                </div>
                <div class="expert-info">
                    <h3>${advisor.name}</h3>
                    <p class="expert-title">${expertiseTags[0] || 'Consultant'}</p>
                    <p class="expert-bio">${advisor.bio ? advisor.bio.substring(0, 120) + (advisor.bio.length > 120 ? '...' : '') : 'No bio available.'}</p>
                    <p>Rate: $${formattedRate}/hour</p>
                    <button class="btn-schedule" data-advisor-id="${advisor.id}" data-advisor-name="${advisor.name}">Schedule Call</button>
                </div>
            `;

            expertsGrid.appendChild(expertCard);
        });

        // Use event delegation for the schedule buttons
        document.body.addEventListener('click', function(event) {
            const scheduleButton = event.target.closest('.btn-schedule');
            if (scheduleButton) {
                event.preventDefault();
                event.stopPropagation();
                const advisorId = scheduleButton.getAttribute('data-advisor-id');
                const advisorName = scheduleButton.getAttribute('data-advisor-name');
                console.log(`Scheduling call with ${advisorName} (ID: ${advisorId})`);
                // Redirect to schedule-call.html with advisor ID
                window.location.href = `schedule-call.php?advisor_id=${advisorId}&advisor_name=${advisorName}`;
            }
        });
    })
    .catch(error => console.error('Error fetching advisors:', error));
});
