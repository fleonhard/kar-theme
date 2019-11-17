/*
 * @author Florian Herborn
 * @copyright 2019 Herborn Software
 * @license GPL-2.0-or-later
 *
 * @package kar
 */

jQuery = require('jquery');

jQuery(document).ready(function ($) {

    function createFullscreenImageModal(toggle) {
        const modal = document.createElement('div');
        const imgSource = $(toggle.data('img'));
        const modalImg = imgSource.clone();
        const x = document.createElement('span');
        const parent = $(toggle).parent()[0];

        x.innerHTML = '&times';

        parent.appendChild(modal);
        $(modal).append(modalImg);
        $(modal).append(x);

        $(x).css({
            position: 'absolute',
            top: 40,
            right: 40,
            'font-size': 36 + 'px',
            color: 'white',
            'text-shadow': '1px 1px black',
            cursor: 'pointer'
        });

        $(modalImg).css({
            'max-width': 100 + '%',
            'max-height': 100 + '%',
            'object-fit': 'contain',
            cursor: 'default'
        });

        const defaultProps = {
            top: $(imgSource).offset().top - $(window).scrollTop(),
            left: $(imgSource).offset().left - $(window).scrollLeft(),
            width: $(imgSource).width(),
            height: $(imgSource).height(),
            opacity: 0,
        };

        const animatedProps = {
            top: 0,
            left: 0,
            width: '100vw',
            height: '100vh',
            opacity: 1
        };

        $(modal).css({
            position: 'fixed',
            'z-index': 10,
            'background-color': 'rgba(0,0,0,0.7)',
            ...defaultProps
        });

        $(modal).animate(animatedProps, 360, 'swing');

        $(x).on('click', function () {
            $(modal).animate(defaultProps, 360, 'swing', function () {
                parent.removeChild(modal);
            });
        });

        $(modal).innerText = "ALKSCMNVÖKM";
    }

    $('.fullscreen-image-toggle').each(function () {
        const toggle = $(this);
        toggle.on('click', function () {
            createFullscreenImageModal(toggle)
        })
    });

    const modal = document.getElementById("fullscreen-modal");

    const img = document.getElementById("image-preview");
    if (img) {
        $(img).on('click', function () {
            //modal.style.display = "block";
            $(modal).fadeIn(1000);
            $(modal).on('click', function () {
                $(modal).fadeOut(1000);
            });
        });
    }
});