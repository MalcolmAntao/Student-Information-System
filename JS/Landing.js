document.getElementById('theme-toggle').addEventListener('click', function () {
    // Toggle light/dark mode on the body
    document.body.classList.toggle('dark-mode');
    document.body.classList.toggle('light-mode');

    // Change the middle layer image based on the current mode
    const middleImage = document.getElementById('middle-img');
    if (document.body.classList.contains('dark-mode')) {
        middleImage.src = '/Assets/Darkmode.svg'; // Dark mode image
        this.textContent = '🌙'; // Sun icon for light mode
    } else {
        middleImage.src = '/Assets/Lightmode.svg'; // Light mode image
        this.textContent = '🌞'; // Moon icon for dark mode
    }
});
