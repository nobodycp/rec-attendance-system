(function () {
    function initSignaturePad(canvasId, inputId, clearBtnId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var hasDrawn = false;

        function resize() {
            var rect = canvas.getBoundingClientRect();
            var w = Math.max(rect.width, 300);
            var h = Math.max(rect.height, 150);
            canvas.width = w;
            canvas.height = h;
            ctx.strokeStyle = '#1e293b';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        function pos(e) {
            var rect = canvas.getBoundingClientRect();
            var scaleX = canvas.width / rect.width;
            var scaleY = canvas.height / rect.height;
            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function start(e) {
            e.preventDefault();
            drawing = true;
            var p = pos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            hasDrawn = true;
            var p = pos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        }

        function stop() {
            drawing = false;
        }

        resize();
        window.addEventListener('resize', function () {
            var saved = hasDrawn ? canvas.toDataURL() : null;
            resize();
            if (saved) {
                var img = new Image();
                img.onload = function () { ctx.drawImage(img, 0, 0); };
                img.src = saved;
            }
        });

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stop);
        canvas.addEventListener('mouseleave', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stop);

        var clearBtn = document.getElementById(clearBtnId);
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
            });
        }

        var form = canvas.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!hasDrawn) {
                    e.preventDefault();
                    alert('يرجى التوقيع الإلكتروني في المربع أدناه قبل الإرسال.');
                    return;
                }
                var input = document.getElementById(inputId);
                if (input) {
                    input.value = canvas.toDataURL('image/png');
                }
            });
        }

        return { resize: resize };
    }

    function boot() {
        initSignaturePad('signature-checkin', 'signature-data-checkin', 'clear-checkin');
        initSignaturePad('signature-checkout', 'signature-data-checkout', 'clear-checkout');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
