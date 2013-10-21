var slideOpts = {
    sl: ['slin', 'slout' ],
    sr: ['srin', 'srout' ],
    popin: ['popin', 'noanim'],
    popout: ['noanim', 'popout']
};

var clearNode = function (node) {
    while (node.firstChild) {
        node.removeChild(node.firstChild);
    }
};

var Slide = function (slideType, vin, vout, callback) {
    var vIn = $('#'+vin),
        vOut = $('#'+vout),
        onAnimationEnd = function () {
            vOut.classList.add('hidden');
            vIn.classList.remove(slideOpts[slideType][0]);
            vOut.classList.remove(slideOpts[slideType][1]);
            vOut.removeEventListener('webkitAnimationEnd', onAnimationEnd, false);
            vOut.removeEventListener('animationend', onAnimationEnd);
        };
    vOut.addEventListener('webkitAnimationEnd', onAnimationEnd, false);
    vOut.addEventListener('animationend', onAnimationEnd);
    if (callback && typeof(callback) === 'function') {
        callback();
    }
    vIn.classList.remove('hidden');
    vIn.classList.add(slideOpts[slideType][0]);
    vOut.classList.add(slideOpts[slideType][1]);
};

var ScrollTop = function () {
    var el = this.parentNode.parentNode.childNodes[5],
        offset = el.scrollTop,
        interval = setInterval(function () {
            el.scrollTop = offset;
            offset -= 24;
            if (offset <= -24) {
                clearInterval(interval);
            }
        }, 8);
};

var TextboxResize = function (el) {
    el.removeEventListener('click', ScrollTop, false);
    el.addEventListener('click', ScrollTop, false);
    var leftbtn = el.parentNode.querySelectorAll('button.left')[0];
    var rightbtn = el.parentNode.querySelectorAll('button.right')[0];
    if (typeof leftbtn === 'undefined') {
        leftbtn = {
            offsetWidth: 0,
            className: ''
        };
    }
    if (typeof rightbtn === 'undefined') {
        rightbtn = {
            offsetWidth: 0,
            className: ''
        };
    }
    var margin = Math.max(leftbtn.offsetWidth, rightbtn.offsetWidth);
    el.style.marginLeft = margin + 'px';
    el.style.marginRight = margin + 'px';
    if (el.offsetWidth < el.scrollWidth) {
        if (leftbtn.offsetWidth < rightbtn.offsetWidth) {
            el.style.marginLeft = leftbtn.offsetWidth + 'px';
            el.style.textAlign = 'right';
        } else {
            el.style.marginRight = rightbtn.offsetWidth + 'px';
            el.style.textAlign = 'left';
        }
        if (el.offsetWidth < el.scrollWidth) {
            if (new RegExp('arrow').test(leftbtn.className)) {
                clearNode(leftbtn.childNodes[1]);
                el.style.marginLeft = '26px';
            }
            if (new RegExp('arrow').test(rightbtn.className)) {
                clearNode(rightbtn.childNodes[1]);
                el.style.marginRight = '26px';
            }
        }
    }
};

$(document).ready(function() {
    FastClick.attach(document.body);

    $('h1').each(function() {
        TextboxResize(this);
    });

    var listitems = document.querySelectorAll('#view-home li'),
        listitemAction = function(){
            Slide('sl', 'view-forms', 'view-home');
        };
    for ( i = listitems.length; i--;) {
        listitems[i].addEventListener('click', listitemAction);
    }
});