var wrapper = document.getElementById("signature-pad");
var clearButton = wrapper.querySelector("[data-action=clear]");
var undoButton = wrapper.querySelector("[data-action=undo]");

var canvas = wrapper.querySelector("canvas");
var signaturePad = new SignaturePad(canvas, {
    // It's Necessary to use an opaque color when saving image as JPEG;
    // this option can be omitted if only saving as PNG or SVG
    backgroundColor: 'rgb(255, 255, 255)'
});

// Adjust canvas coordinate space taking into account pixel ratio,
// to make it look crisp on mobile devices.
// This also causes canvas to be cleared.
function resizeCanvas() {
    // When zoomed out to less than 100%, for some very strange reason,
    // some browsers report devicePixelRatio as less than 1
    // and only part of the canvas is cleared then.
    var ratio = Math.max(window.devicePixelRatio || 1, 1);

    // This part causes the canvas to be cleared
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
}

// On mobile devices it might make more sense to listen to orientation change,
// rather than window resize events.
// window.onresize = resizeCanvas;
$(document).ready(function () {
    resizeCanvas();
    $("#email-input").hide();
});

$('.canvas-pad').on('click touchstart', function () {
    var form = document.getElementById("myform");
    for (var i = 0; i < form.elements.length; i++) {
        form.elements[i].blur();
    }
});


clearButton.addEventListener("click", function (event) {
    signaturePad.clear();
});

undoButton.addEventListener("click", function (event) {
    var data = signaturePad.toData();

    if (data) {
        data.pop(); // remove the last dot or line
        signaturePad.fromData(data);
    }
});

// Opening animation

$.Velocity.RegisterEffect('fadeInPanels', {
    defaultDuration: 500,
    calls: [
        [{
            opacity: [1, 0.3],
            scale: [1, 0.5]
        }]
    ]
});

$('.panel').velocity('fadeInPanels', {
    stagger: 250,
})

// Accordions

$('.accordion-link').click(function (e) {
    e.preventDefault();

    var accordionWrapper = $(this).closest('.accordion'),
        accordionActivePanel = $(this).closest('.panel'),
        accordionActiveLink = accordionWrapper.find('.active');

    if ($(this).closest('.panel').find('.content-collapse').hasClass('velocity-animating')) {
        return false;
    } else if (e.handled !== true) {
        accordionActiveLink.closest('.panel').find('.content-collapse').attr('aria-expanded', false).velocity('slideUp', {
            easing: 'easeOutQuad'
        });
        if ($(this).hasClass('active')) {
            $(this).attr('aria-expanded', false).removeClass('active');
        } else {
            accordionActivePanel.find('.content-collapse').attr('aria-expanded', true).velocity('slideDown', {
                easing: 'easeOutQuad'
            });
            accordionWrapper.find('.accordion-link').removeClass('active').attr('aria-expanded', false);
            $(this).attr('aria-expanded', true).addClass('active');
        }
        e.handled = true;
    } else {
        return false;
    }
});

// Toggles

$('.toggle-link').click(function (e) {
    e.preventDefault();
    var toggleActivePanel = $(this).closest('.panel');

    if (toggleActivePanel.find('.content-collapse').hasClass('velocity-animating')) {
        return false;
    } else if (e.handled !== true) {
        if ($(this).hasClass('active')) {
            toggleActivePanel.find('.content-collapse').attr('aria-expanded', false).velocity('slideUp', {
                easing: 'easeOutQuad'
            });
            $(this).attr('aria-expanded', false).removeClass('active');
        } else {
            toggleActivePanel.find('.content-collapse').attr('aria-expanded', true).velocity('slideDown', {
                easing: 'easeOutQuad'
            });
            $(this).attr('aria-expanded', true).addClass('active');
        }
        e.handled = true;
    } else {
        return false;
    }
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

$(function () {
    $("input[name='born']").on('input', function (e) {
        $(this).val($(this).val().replace(/[^0-9\/]/g, ''));
        if ($(this).val().length == 10 && isValidDate($(this).val()) == false) $(this).val("");
    });
});

$(function () {
    $("input[name='release-data']").on('input', function (e) {
        $(this).val($(this).val().replace(/[^0-9\/]/g, ''));
        if ($(this).val().length == 10 && isValidDate($(this).val()) == false) $(this).val("");
    });
});

$("#complete-p").hide();
$('#myform').submit(function (event) {
    // event.preventDefault();
    var dataUrl = signaturePad.toDataURL();
    var imagen = dataUrl.replace(/^data:image\/(png|jpg);base64,/, "");
    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "imageData").val(imagen);
    $('#myform').append(input);

    if (isValidDate($("input[name=born]").val()) == false) {
        $("input[name=born]").val("");
        return false;
    }
    if (isValidDate($("input[name=release-data]").val()) == false) {
        $("input[name=release-data]").val("");
        return false;
    }

    $("#complete-p").show();
    if (idButton == "download") $("#complete-p").find("span").text("Attendi, il tuo download partirà a breve...");
    else $("#complete-p").find("span").text("Attendi, riceverai a breve una mail con la tua autocertificazione...");

    return true;
});

$(document).ready(function () {
    $('li.active').removeClass('active');
    $('a[href="' + location.pathname + '"]').closest('li').addClass('active');
});

var outputSelect = "download";

function selectOutput(idButton) {
    outputSelect = idButton;
    if (idButton == "download") {
        $("#download").removeClass("blu-button-bold-upper");
        $("#download").addClass("blu-button-bold-upper-active");

        $("#email").removeClass("blu-button-bold-upper-active");
        $("#email").addClass("blu-button-bold-upper");

        $("#email-input").hide();
        $("#email-input").prop('required', false);
    } else {
        $("#email").removeClass("blu-button-bold-upper");
        $("#email").addClass("blu-button-bold-upper-active");

        $("#download").removeClass("blu-button-bold-upper-active");
        $("#download").addClass("blu-button-bold-upper");

        $("#email-input").show();
        $("#email-input").prop('required', true);
    }
}

function isValidDate(s) {
    var bits = s.split('/');
    var d = new Date(bits[1] + '/' + bits[0] + '/' + bits[2]);
    return !!(d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[0]));
}