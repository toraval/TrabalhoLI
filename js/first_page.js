document.addEventListener("DOMContentLoaded", () => {
    const hero = document.querySelector(".hero-content");
    hero.style.opacity = "0";
    hero.style.transform = "translateY(20px)";

    setTimeout(() => {
        hero.style.transition = "0.6s";
        hero.style.opacity = "1";
        hero.style.transform = "translateY(0)";
    }, 200);
});