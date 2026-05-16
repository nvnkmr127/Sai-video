<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - {{ $siteSettings['site_name'] ?? 'WorkshopPro' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #111827; color: #fff; font-family: system-ui, -apple-system, sans-serif; overflow-x: hidden; }
        .scanner-container { max-width: 500px; margin: 0 auto; padding: 20px; position: relative; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; border: 2px solid #374151; background: #1f2937; position: relative; }
        
        /* Flash Overlay */
        #flashOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 9999; display: none; pointer-events: none;
            opacity: 0; transition: opacity 0.1s ease-out;
        }

        .result-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9); z-index: 10000;
            display: none; align-items: center; justify-content: center;
            text-align: center; padding: 20px;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .result-content { 
            transform: scale(0.8); animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            background: #1f2937; border-radius: 24px; padding: 40px 20px; width: 100%; max-width: 400px;
            border: 2px solid #374151;
        }

        @keyframes popIn { to { transform: scale(1); } }
        
        .icon-circle {
            width: 80px; height: 80px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 40px;
            margin: 0 auto 20px;
        }

        .success-accent { border-color: #10b981 !important; }
        .error-accent { border-color: #ef4444 !important; }

        .btn-scan { padding: 15px; font-weight: 700; border-radius: 12px; font-size: 1.1rem; }
        #reader__dashboard_section_csr button { background: #6366f1 !important; color: white !important; border: none !important; padding: 10px 20px !important; border-radius: 8px !important; }
        .progress { background-color: #374151; }
    </style>
</head>
<body>
    <div class="scanner-container">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-indigo-400">{{ $siteSettings['site_name'] ?? 'WorkshopPro' }} Scanner</h3>
            <p class="text-secondary small">Front Desk Validator Mode</p>
        </div>

        <!-- Live Capacity Bar -->
        <div class="text-center mb-3">
            <span class="text-secondary small">Checked in today</span>
            <div class="d-flex align-items-center gap-2 justify-content-center mt-1">
                <span id="checkedInCount" class="fs-3 fw-bold text-success">—</span>
                <span class="text-secondary">/</span>
                <span id="totalSeats" class="fs-5 text-secondary">—</span>
            </div>
            <div class="progress mt-2" style="height: 6px;">
                <div id="capacityBar" class="progress-bar bg-success" style="width: 0%"></div>
            </div>
        </div>

        <div id="reader"></div>

        <div id="flashOverlay"></div>

        <div id="resultOverlay" class="result-overlay">
            <div id="successContent" class="result-content success-accent" style="display: none;">
                <div class="icon-circle bg-success bg-opacity-20 text-success">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h2 class="fw-bold mb-1 attendee-name"></h2>
                <div class="workshop-title text-secondary small mb-4"></div>
                <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2 rounded-pill mb-4">
                    CHECKED IN SUCCESSFULLY
                </div>
                <div class="text-muted small checkin-time"></div>
                <button class="btn btn-outline-secondary w-100 mt-4 py-3 rounded-4" onclick="resetScanner()">CLOSE (3s)</button>
            </div>

            <div id="errorContent" class="result-content error-accent" style="display: none;">
                <div class="icon-circle bg-danger bg-opacity-20 text-danger">
                    <i class="bi bi-x-lg"></i>
                </div>
                <h3 class="fw-bold mb-2 error-message text-danger"></h3>
                <p class="error-detail text-secondary small mb-4"></p>
                <button class="btn btn-danger w-100 py-3 rounded-4" onclick="resetScanner()">TRY AGAIN</button>
            </div>
        </div>

        <div class="mt-4 d-grid gap-2">

        <div class="text-center mt-5">
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">Reset Page</button>
        </div>
    </div>

    <script>
        const html5QrCode = new Html5Qrcode("reader");
        const deskKey = "{{ $key }}";
        const resultOverlay = document.getElementById('resultOverlay');
        const successContent = document.getElementById('successContent');
        const errorContent = document.getElementById('errorContent');
        const flashOverlay = document.getElementById('flashOverlay');
        let isProcessing = false;

        function refreshStats() {
            fetch(`/validate/stats?key=${deskKey}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('checkedInCount').textContent = data.checked_in;
                    document.getElementById('totalSeats').textContent = data.total;
                    const pct = data.total > 0 ? Math.round((data.checked_in / data.total) * 100) : 0;
                    document.getElementById('capacityBar').style.width = pct + '%';
                })
                .catch(err => console.error("Stats refresh failed:", err));
        }

        refreshStats();
        setInterval(refreshStats, 15000);

        function triggerFlash(color) {
            flashOverlay.style.background = color;
            flashOverlay.style.display = 'block';
            flashOverlay.style.opacity = '0.8';
            setTimeout(() => {
                flashOverlay.style.opacity = '0';
                setTimeout(() => { flashOverlay.style.display = 'none'; }, 100);
            }, 100);
        }

        function playFeedback(success) {
            // Flash effect
            triggerFlash(success ? '#10b981' : '#ef4444');

            // Haptic Feedback (Vibration)
            if (navigator.vibrate) {
                if (success) {
                    navigator.vibrate([100, 50, 100]); // Sharp double pulse
                } else {
                    navigator.vibrate([300, 100, 300, 100, 300]); // Long warning pulses
                }
            }

            // Audio Feedback
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.value = success ? 880 : 220;
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.4);
            } catch (e) {
                console.warn("Audio feedback failed:", e);
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            console.log(`Code scanned = ${decodedText}`);
            verifyToken(decodedText);
        }

        function verifyToken(token) {
            isProcessing = true;
            html5QrCode.pause();
            
            // Show loading state
            document.getElementById('manualToken').disabled = true;
            document.getElementById('submitManual').disabled = true;
            document.getElementById('submitManual').innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch("{{ route('registration.validator.check') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ token: token, key: deskKey })
            })
            .then(response => {
                if (!response.ok && response.status !== 409 && response.status !== 404) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                hideAllResults();
                if (data.success) {
                    showSuccess(data);
                    playFeedback(true);
                    refreshStats();
                    setTimeout(resetScanner, 4000);
                } else {
                    showError(data);
                    playFeedback(false);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error: " + (err.message || "Connection failed."));
                resetScanner();
            });
        }

        function resetScanner() {
            isProcessing = false;
            hideAllResults();
            document.getElementById('manualToken').disabled = false;
            document.getElementById('submitManual').disabled = false;
            document.getElementById('submitManual').textContent = 'Verify';
            document.getElementById('manualToken').value = '';
            
            try {
                html5QrCode.resume();
            } catch (e) {
                console.warn("Scanner already resumed or not running");
            }
        }

        function hideAllResults() {
            resultOverlay.style.display = 'none';
            successContent.style.display = 'none';
            errorContent.style.display = 'none';
        }

        function showSuccess(data) {
            successContent.querySelector('.attendee-name').textContent = data.attendee;
            successContent.querySelector('.workshop-title').textContent = data.workshop;
            successContent.querySelector('.checkin-time').textContent = `Verified at: ${data.time}`;
            
            resultOverlay.style.display = 'flex';
            successContent.style.display = 'block';
        }

        function showError(data) {
            errorContent.querySelector('.error-message').textContent = data.message;
            errorContent.querySelector('.error-detail').textContent = data.attendee ? `Already checked in: ${data.attendee} (${data.time})` : 'Invalid or unapproved ticket.';
            
            resultOverlay.style.display = 'flex';
            errorContent.style.display = 'block';
        }

        // Manual Submit
        document.getElementById('submitManual').addEventListener('click', () => {
            const token = document.getElementById('manualToken').value;
            if (token && !isProcessing) verifyToken(token);
        });

        // Start Scanner
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
            .catch(err => {
                console.error("Camera start failed:", err);
                alert("Camera access denied or not available. Please ensure permissions are granted.");
            });

        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (html5QrCode.getState() !== 1) { // 1 = NOT_STARTED
                html5QrCode.stop();
            }
        });

    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
