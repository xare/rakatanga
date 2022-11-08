import './styles/photogallery.css';

let lightbox = document.createElement('div')
lightbox.id = 'lightbox'
document.body.appendChild(lightbox)

let next = document.createElement('button')
next.classList.add('carousel-button')
next.classList.add('next')
next.innerHTML = '&#10097;'
next.dataset.carouselButton = 'next'
let prev = document.createElement('button')
prev.classList.add('carousel-button')
prev.classList.add('prev')
prev.innerHTML = '&#10096;'
prev.dataset.carouselButton = 'prev'

const imageLinks = document.querySelectorAll('a.lightbox-item-link')

imageLinks.forEach(imageLink => {
    const image = imageLink.querySelector('img')

    imageLink.addEventListener('click', event => {
        event.preventDefault()

        lightbox.classList.add('active')
        let content = _displayImage(imageLink)
        if ('video' in imageLink.dataset) {
            content = _displayVideo(imageLink)
        }

        lightbox.innerHTML = ""

        lightbox.appendChild(next)
        lightbox.appendChild(prev)
        lightbox.appendChild(content)

        const buttons = document.querySelectorAll('[data-carousel-button]')
        Array.from(buttons).forEach(button => {
            button.addEventListener('click', (event) => {
                const offset = button.dataset.carouselButton === 'next' ? 1 : -1

                const carousel = document.querySelector('[data-carousel]')
                const activeSlide = carousel.querySelector('[data-active]')

                let newIndex = [...carousel.children].indexOf(activeSlide) + offset

                if (newIndex < 0) newIndex = carousel.children.length - 1
                if (newIndex >= carousel.children.length) newIndex = 0
                let content2 = _displayImage(carousel.children[newIndex])
                if ('video' in carousel.children[newIndex].dataset) {
                    content2 = _displayVideo(carousel.children[newIndex])
                }

                lightbox.innerHTML = ""

                lightbox.appendChild(next)
                lightbox.appendChild(prev)
                lightbox.appendChild(content2)
                carousel.children[newIndex].dataset.active = true
                delete activeSlide.dataset.active;
            })
        })
    })
})

lightbox.addEventListener('click', e => {
    if (e.target !== e.currentTarget) return
    lightbox.classList.remove('active')
})

function _displayImage(element) {
    const img = document.createElement('img')
    img.src = element.href
    img.classList.add('active')
    return img
}

function _displayVideo(element) {
    const videoYTCode = element.dataset.yt
    const videoContainer = document.createElement('div')
    videoContainer.classList.add('video-container')
    const insertYTHtml = `<iframe width="800" height="450" src="https://www.youtube.com/embed/${videoYTCode}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`
    videoContainer.innerHTML = insertYTHtml
    return videoContainer
}