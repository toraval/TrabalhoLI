document.addEventListener("DOMContentLoaded", () => {
    // Hero content animation
    const heroContent = document.querySelector(".hero-content");
    heroContent.style.opacity = "0";
    heroContent.style.transform = "translateY(30px)";

    setTimeout(() => {
        heroContent.style.transition = "0.8s ease";
        heroContent.style.opacity = "1";
        heroContent.style.transform = "translateY(0)";
    }, 300);

    // Card animation
    const container = document.querySelector(".container");
    container.style.opacity = "0";
    container.style.transform = "translateY(40px)";

    setTimeout(() => {
        container.style.transition = "0.8s ease 0.2s";
        container.style.opacity = "1";
        container.style.transform = "translateY(0)";
    }, 1000);

    // Button hover effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Scroll indicator functionality
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', () => {
            document.querySelector('.container').scrollIntoView({ 
                behavior: 'smooth' 
            });
        });
    }
});