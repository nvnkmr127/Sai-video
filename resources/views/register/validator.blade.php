<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - {{ $siteSettings['site_name'] ?? 'WorkshopPro' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #111827; color: #fff; font-family: system-ui, -apple-system, sans-serif; }
        .scanner-container { max-width: 500px; margin: 0 auto; padding: 20px; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; border: 2px solid #374151; background: #1f2937; }
        .result-card { display: none; margin-top: 20px; border-radius: 12px; padding: 20px; animation: slideUp 0.3s ease-out; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
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

        <div class="mt-4 d-grid gap-2">
            <div class="input-group">
                <input type="text" id="manualToken" class="form-control bg-dark text-white border-secondary" placeholder="Enter token manually...">
                <button class="btn btn-primary" type="button" id="submitManual">Verify</button>
            </div>
        </div>

        <div id="resultArea">
            <!-- Success Card -->
            <div id="successCard" class="result-card bg-success text-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0 attendee-name"></h5>
                        <p class="mb-0 workshop-title small"></p>
                        <small class="checkin-time"></small>
                    </div>
                </div>
                <div class="mt-3 small fw-bold">CHECKED IN SUCCESSFULLY</div>
            </div>

            <!-- Error Card -->
            <div id="errorCard" class="result-card bg-danger text-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0 error-message"></h5>
                        <p class="mb-0 error-detail small"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">Reset Page</button>
        </div>
    </div>

    <script>
        const html5QrCode = new Html5Qrcode("reader");
        const deskKey = "{{ $key }}";
        const resultArea = document.getElementById('resultArea');
        const successCard = document.getElementById('successCard');
        const errorCard = document.getElementById('errorCard');
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

        function playBeep(success) {
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
                // Mobile vibration
                if (navigator.vibrate) navigator.vibrate(success ? [100] : [200, 100, 200]);
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
                hideAllCards();
                if (data.success) {
                    showSuccess(data);
                    playBeep(true);
                    refreshStats();
                    setTimeout(resetScanner, 3000);
                } else {
                    showError(data);
                    playBeep(false);
                    setTimeout(resetScanner, 5000);
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
            hideAllCards();
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

        function hideAllCards() {
            successCard.style.display = 'none';
            errorCard.style.display = 'none';
        }

        function showSuccess(data) {
            successCard.querySelector('.attendee-name').textContent = data.attendee;
            successCard.querySelector('.workshop-title').textContent = data.workshop;
            successCard.querySelector('.checkin-time').textContent = `Time: ${data.time}`;
            successCard.style.display = 'block';
        }

        function showError(data) {
            errorCard.querySelector('.error-message').textContent = data.message;
            errorCard.querySelector('.error-detail').textContent = data.attendee ? `Attendee: ${data.attendee} (${data.time})` : 'Try again or enter manually.';
            errorCard.style.display = 'block';
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
