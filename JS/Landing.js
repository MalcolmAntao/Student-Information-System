// Function to set the mode based on localStorage
function setThemeMode(mode) {
    const middleImage = document.getElementById('middle-img');
    if (mode === 'dark') {
        document.body.classList.add('dark-mode');
        document.body.classList.remove('light-mode');
        middleImage.src = '../Assets/Darkmode.png'; // Dark mode image
        document.getElementById('theme-toggle').innerHTML = '<img src="../Assets/Light_Mode.svg" alt="Light Mode Icon" />' // Sun icon for light mode
    } else {
        document.body.classList.add('light-mode');
        document.body.classList.remove('dark-mode');
        middleImage.src = '../Assets/Lightmode.png'; // Light mode image
        document.getElementById('theme-toggle').innerHTML = '<img src="../Assets/Dark_Mode.svg" alt="Light Mode Icon" />' // Moon icon for dark mode
    }
}

// On page load, check if a mode is stored in localStorage
document.addEventListener('DOMContentLoaded', function () {
    const storedMode = localStorage.getItem('themeMode') || 'light'; // Default to light mode if nothing is stored
    setThemeMode(storedMode);
});

// Toggle theme and save the choice to localStorage
document.getElementById('theme-toggle').addEventListener('click', function () {
    let currentMode = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    currentMode = currentMode === 'dark' ? 'light' : 'dark'; // Toggle mode
    setThemeMode(currentMode);
    localStorage.setItem('themeMode', currentMode); // Save mode to localStorage
});
