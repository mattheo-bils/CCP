(function(){
    "use strict";
    const slideTimeout = 5000;
    const prev = document.querySelector('#prev');
    const next = document.querySelector('#next');
    const $slides = document.querySelectorAll('.slide');
    let $dots;
    let intervalId;
    let currentSlide = 0;

    function slideTo(index){
        if (index >= $slides.length) index = 0;
        if (index < 0) index = $slides.length - 1;
        currentSlide = index;
        $slides.forEach($elt => $elt.style.transform = `translateX(-${currentSlide * 100}%)`);
        $dots.forEach(($elt, key) => $elt.className = `dot ${key === currentSlide ? 'active' : 'inactive'}`);
    }

    function showSlide(){
        slideTo(currentSlide);
        currentSlide++;
    }

    for (let i = 0; i < $slides.length; i++){
        let dotClass = i === currentSlide ? 'active' : 'inactive';
        let $dot = `<span data-slideId="${i}" class="dot ${dotClass}"></span>`;
        document.querySelector('.carousel_dots').innerHTML += $dot;
    }

    $dots = document.querySelectorAll('.dot');
    $dots.forEach(($elt, key) => $elt.addEventListener('click', () => slideTo(key)));
    prev.addEventListener('click', () => slideTo(--currentSlide));
    next.addEventListener('click', () => slideTo(++currentSlide));
    intervalId = setInterval(showSlide, slideTimeout);

    $slides.forEach($elt => {
        let startX, endX;
        $elt.addEventListener('mouseover', () => clearInterval(intervalId));
        $elt.addEventListener('mouseout', () => { intervalId = setInterval(showSlide, slideTimeout); });
        $elt.addEventListener('touchstart', (event) => { startX = event.touches[0].clientX; });
        $elt.addEventListener('touchend', (event) => {
            endX = event.changedTouches[0].clientX;
            if (startX > endX) slideTo(currentSlide + 1);
            else if (startX < endX) slideTo(currentSlide - 1);
        });
    });
})()
// Rendre les cards de tendance cliquables
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.tendance .card').forEach(card => {
        card.style.cursor = 'pointer';
        const titre = card.querySelector('.card_text p:first-child').textContent.trim().toLowerCase();
        const produit = produits.find(p => p.titre.toLowerCase() === titre);
        if (produit) {
            card.addEventListener('click', (e) => {
                if (e.target.classList.contains('ajouter')) return;
                window.location.href = `produit.html?id=${produit.id}`;
            });
        }
    });
});