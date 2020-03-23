function CreatePDFfromHTML() {

    var nome = $('#nome').val();
    if (nome == "") {
        alert("Inserire Nome e Cognome");
        return false;
    }
    var nascita = $('#nascita').val();
    if (nascita == "") {
        alert("Inserire data di nascita");
        return false;
    }
    var comunenascita = $('#comunenascita').val();
    if (comunenascita == "") {
        alert("Inserire il comune di nascita");
        return false;
    }
    var comuneresidenza = $('#comuneresidenza').val();
    if (comuneresidenza == "") {
        alert("Inserire il comune di residenza");
        return false;
    }
    var via = $('#via').val();
    if (via == "") {
        alert("Inserire via e numero civico");
        return false;
    }
    var doc = $('#doc').val();
    if (doc == "") {
        alert("Scegliere un documento valido");
        return false;
    }
    var numerodoc = $('#numerodoc').val();
    if (numerodoc == "") {
        alert("Inserire numero documento");
        return false;
    }
    var comunedatarilascio = $('#comunedatarilascio').val();
    if (comunedatarilascio == "") {
        alert("Inserire il comune e la data di rilascio");
        return false;
    }
    var telefono = $('#telefono').val();
    if (telefono == "") {
        alert("Inserire un recapito telefonico");
        return false;
    }
    var dichiarazione = $('#dichiarazione').val();
    if (dichiarazione == "") {
        alert("Inserire testo dichiarazione");
        return false;
    }

    var motivo = document.querySelector('input[name="motivo"]:checked').value;


    document.getElementById("form13_1").value = nome;
    document.getElementById("form5_1").value = nascita;
    document.getElementById("form6_1").value = comunenascita;
    document.getElementById("form9_1").value = comuneresidenza;
    document.getElementById("form12_1").value = via;
    document.getElementById("form10_1").value = doc;
    document.getElementById("form11_1").value = numerodoc;
    document.getElementById("form7_1").value = comunedatarilascio;
    document.getElementById("form8_1").value = telefono;

    console.log(motivo);
    if (motivo == 'Scelta1') {
        document.getElementById("form4_1").checked = true;
    } else if (motivo == 'Scelta2') {
        document.getElementById("form2_1").checked = true;
    } else if (motivo == 'Scelta3') {
        document.getElementById("form1_1").checked = true;
    } else {
        document.getElementById("form3_1").checked = true;
    }

    document.getElementById("form15_1").value = dichiarazione;



    $(".pageArea").show();
    var HTML_Width = $(".pageArea").width();
    var HTML_Height = $(".pageArea").height();
    var top_left_margin = 15;
    var PDF_Width = HTML_Width + (top_left_margin * 2);
    var PDF_Height = (PDF_Width * 1.5) + (top_left_margin * 2);
    var canvas_image_width = HTML_Width;
    var canvas_image_height = HTML_Height;

    var totalPDFPages = Math.ceil(HTML_Height / PDF_Height) - 1;

    html2canvas($(".pageArea")[0]).then(function (canvas) {
        var imgData = canvas.toDataURL("image/jpeg", 1.0);
        var pdf = new jsPDF('p', 'pt', [PDF_Width, PDF_Height]);
        pdf.addImage(imgData, 'JPG', top_left_margin, top_left_margin, canvas_image_width, canvas_image_height);
        for (var i = 1; i <= totalPDFPages; i++) {
            pdf.addPage(PDF_Width, PDF_Height);
            pdf.addImage(imgData, 'JPG', top_left_margin, -(PDF_Height * i) + (top_left_margin * 4), canvas_image_width, canvas_image_height);
        }
        var image = $('#sig-dataUrl').val();
        pdf.addImage(image, 'JPEG', 100, 1205, 250, 100);
        pdf.save("modulo-autodichiarazione-17.3.2020.pdf");

        $(".pageArea").hide();
    });








}


(function () {
    window.requestAnimFrame = (function (callback) {
        return window.requestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            window.oRequestAnimationFrame ||
            window.msRequestAnimaitonFrame ||
            function (callback) {
                window.setTimeout(callback, 1000 / 60);
            };
    })();

    var canvas = document.getElementById("sig-canvas");
    var ctx = canvas.getContext("2d");
    ctx.strokeStyle = "#222222";
    ctx.lineWidth = 4;

    var drawing = false;
    var mousePos = {
        x: 0,
        y: 0
    };
    var lastPos = mousePos;

    canvas.addEventListener("mousedown", function (e) {
        drawing = true;
        lastPos = getMousePos(canvas, e);
    }, false);

    canvas.addEventListener("mouseup", function (e) {
        drawing = false;
    }, false);

    canvas.addEventListener("mousemove", function (e) {
        mousePos = getMousePos(canvas, e);
    }, false);

    // Add touch event support for mobile
    canvas.addEventListener("touchstart", function (e) {

    }, false);

    canvas.addEventListener("touchmove", function (e) {
        var touch = e.touches[0];
        var me = new MouseEvent("mousemove", {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(me);
    }, false);

    canvas.addEventListener("touchstart", function (e) {
        mousePos = getTouchPos(canvas, e);
        var touch = e.touches[0];
        var me = new MouseEvent("mousedown", {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(me);
    }, false);

    canvas.addEventListener("touchend", function (e) {
        var me = new MouseEvent("mouseup", {});
        canvas.dispatchEvent(me);
    }, false);

    function getMousePos(canvasDom, mouseEvent) {
        var rect = canvasDom.getBoundingClientRect();
        return {
            x: mouseEvent.clientX - rect.left,
            y: mouseEvent.clientY - rect.top
        }
    }

    function getTouchPos(canvasDom, touchEvent) {
        var rect = canvasDom.getBoundingClientRect();
        return {
            x: touchEvent.touches[0].clientX - rect.left,
            y: touchEvent.touches[0].clientY - rect.top
        }
    }

    function renderCanvas() {
        if (drawing) {
            ctx.moveTo(lastPos.x, lastPos.y);
            ctx.lineTo(mousePos.x, mousePos.y);
            ctx.stroke();
            lastPos = mousePos;
        }
    }

    // Prevent scrolling when touching the canvas
    document.body.addEventListener("touchstart", function (e) {
        if (e.target == canvas) {
            e.preventDefault();
        }
    }, false);
    document.body.addEventListener("touchend", function (e) {
        if (e.target == canvas) {
            e.preventDefault();
        }
    }, false);
    document.body.addEventListener("touchmove", function (e) {
        if (e.target == canvas) {
            e.preventDefault();
        }
    }, false);

    (function drawLoop() {
        requestAnimFrame(drawLoop);
        renderCanvas();
    })();

    function clearCanvas() {
        canvas.width = canvas.width;
    }

    // Set up the UI
    var sigText = document.getElementById("sig-dataUrl");
    var sigImage = document.getElementById("sig-image");
    var clearBtn = document.getElementById("sig-clearBtn");
    var submitBtn = document.getElementById("sig-submitBtn");
    clearBtn.addEventListener("click", function (e) {
        clearCanvas();
        sigText.innerHTML = "Data URL for your signature will go here!";
        sigImage.setAttribute("src", "");
    }, false);
    submitBtn.addEventListener("click", function (e) {
        var dataUrl = canvas.toDataURL();
        sigText.innerHTML = dataUrl;
        sigImage.setAttribute("src", dataUrl);
    }, false);

})();

function myFunction() {
    var x = document.getElementById("myLinks");
    if (x.style.display === "block") {
        x.style.display = "none";
    } else {
        x.style.display = "block";
    }
}