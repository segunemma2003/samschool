export class QuizRecording {
    constructor() {
        this.mediaRecorder = null;
        this.recordedChunks = [];
        this.stream = null;
    }

    async startRecording() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 320 },
                    height: { ideal: 240 }
                },
                audio: true
            });

            this.mediaRecorder = new MediaRecorder(this.stream, {
                mimeType: 'video/webm;codecs=vp8,opus',
                videoBitsPerSecond: 100000 // Lower bitrate for smaller file size
            });

            this.mediaRecorder.ondataavailable = this.handleDataAvailable.bind(this);
            this.mediaRecorder.start(1000); // Capture in 1-second chunks

        } catch (error) {
            console.error('Recording failed:', error);
            Livewire.dispatch('recordingFailed', { error: error.message });
        }
    }

    handleDataAvailable(event) {
        if (event.data.size > 0) {
            this.recordedChunks.push(event.data);

            // Convert chunk to base64 and send to server
            const reader = new FileReader();
            reader.onloadend = () => {
                const base64data = reader.result.split(',')[1];
                Livewire.dispatch('recordingChunk', { chunk: base64data });
            };
            reader.readAsDataURL(event.data);
        }
    }

    stopRecording() {
        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
            this.mediaRecorder.stop();
            this.stream.getTracks().forEach(track => track.stop());
        }
    }
}
