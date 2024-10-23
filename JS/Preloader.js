// JavaScript to handle the preloader
window.addEventListener('load', function () {
    const preloader = document.getElementById('preloader');
    
    // Add the fade-out class to trigger CSS transition
    preloader.classList.add('fade-out');
  
    // Optional: Remove preloader from the DOM after fading out
    setTimeout(() => {
      preloader.style.display = 'none'; // Hides the preloader
    }, 800); // Match this duration with the CSS transition time
  });
  