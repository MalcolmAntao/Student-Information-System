 // Function to set the theme mode and update elements based on mode
 function setThemeMode(mode) {
    const middleImage = document.getElementById('middle-img');
    const themeToggleIcon = document.querySelector('#theme-toggle ion-icon');

    if (mode === 'dark') {
        document.body.classList.add('dark-mode');
        document.body.classList.remove('light-mode');
        middleImage.src = '../Assets/Darkmode.png'; // Dark mode image
        themeToggleIcon.setAttribute('name', 'moon-outline'); // Moon icon for dark mode
    } else {
        document.body.classList.add('light-mode');
        document.body.classList.remove('dark-mode');
        middleImage.src = '../Assets/Lightmode.png'; // Light mode image
        themeToggleIcon.setAttribute('name', 'sunny-outline'); // Sun icon for light mode
    }
}

// On page load, apply the last saved theme from localStorage
document.addEventListener('DOMContentLoaded', function () {
    const savedMode = localStorage.getItem('themeMode') || 'light'; // Defaults to light if not set
    setThemeMode(savedMode); // Apply saved mode on load
});

// Toggle theme on button click, then save the new mode in localStorage
document.getElementById('theme-toggle').addEventListener('click', function () {
    const currentMode = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    const newMode = currentMode === 'dark' ? 'light' : 'dark'; // Toggle mode
    setThemeMode(newMode);
    localStorage.setItem('themeMode', newMode); // Save new mode to localStorage
}); 