(function(){
    "use stict"
    const slideTimeout = 5000;
    const prev = document.querySelector('#prev');
    const next = document.querySelector('#next');
    const $slides = document.querySelectorAll('.slide');
    let $dots;
    let intervalId;
    let currentSlide=1;
    function slideTo(index){
        currentSlide = index >= $slides.length || index < 1 ? 0 : index;
        $slides.forEach($elt => $elt.style.transform = `translateX(-${currentSlide * 100}%)`);
        $dots.forEach(($elt, key) => $elt.classList = `dot ${key === currentSlide? 'active': 'inactive'}`);
    }
    function showSlide(){
        slideTo(currentSlide);
        currentSlide++;
    }
    for (let i=1; i<= $slides.length;i++){
        let dotClass = i == currentSlide ? 'active' : 'inactive';
        let $dot = '<span data-slideId="${i}" class= "dot ${dotClass}"></span>';
        document.querySelector('.carousel-dots').innerHTML += $dot;
    }
    $dots = document.querySelectorAll('.dot');
    $dots.forEach(($elt, key) => $elt.addEventListener('click', () => slideTo(key)));
    prev.addEventListener('click', () => slideTo(--currentSlide));
    next.addEventListener('click', () => slideTo(++currentSlide));
    intervalId = setInterval(showSlide, slideTimeout)
    $slides
})