<div 
    x-data="{
        scanner: null,
        errorMessage: null,

        initScanner() {
            if (typeof Html5QrcodeScanner === 'undefined') {
                this.errorMessage = 'Scanner library not loaded. Please refresh.';
                return;
            }

            this.$nextTick(() => {
                this.scanner = new Html5QrcodeScanner('reader', { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 }, // Restrict scanning area
                    aspectRatio: 1.0,
                    rememberLastUsedCamera: true
                });
                
                this.scanner.render(this.onScanSuccess, this.onScanFailure);
            });
        },

        onScanSuccess(decodedText) {
            if (this.scanner) this.scanner.clear();
            
            let audio = new Audio('https://media.geeksforgeeks.org/wp-content/uploads/20190531135120/beep.mp3');
            audio.play().catch(e => console.log('Audio blocked'));

            window.location.href = '/admin/bookings?tableSearch=' + encodeURIComponent(decodedText.trim());
        },

        onScanFailure(error) {}
    }"
    x-init="initScanner()"
    style="display: flex; flex-direction: column; align-items: center;"
>
    <div x-show="errorMessage" class="text-danger-600 font-bold mb-2" x-text="errorMessage"></div>

    <div style="width: 100%; max-width: 400px; border-radius: 10px; overflow: hidden; background: #000;">
        <div id="reader" style="width: 100%;"></div>
    </div>
    
    <p class="text-sm text-gray-500 mt-3">
        Point camera at the Booking Reference QR.
    </p>
</div>