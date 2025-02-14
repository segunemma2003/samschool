// security.js
export class QuizSecurity {
    constructor() {
        this.fullscreenEnabled = false;
        this.originalWindowId = crypto.randomUUID();
        this.setupEventListeners();
    }

    setupEventListeners() {
        document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
        window.addEventListener('blur', this.handleWindowBlur.bind(this));
        window.addEventListener('focus', this.handleWindowFocus.bind(this));
        window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
    }

    enforceFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen()
                .then(() => {
                    this.fullscreenEnabled = true;
                })
                .catch(err => {
                    Livewire.dispatch('securityIncident', {
                        type: 'FULLSCREEN_FAILED',
                        details: err.message
                    });
                });
        }
    }

    handleVisibilityChange() {
        if (document.hidden) {
            Livewire.dispatch('tabChanged');
        }
    }

    handleWindowBlur() {
        Livewire.dispatch('securityIncident', {
            type: 'WINDOW_BLUR',
            timestamp: new Date().toISOString()
        });
    }

    preventMultipleWindows() {
        const windowId = sessionStorage.getItem('quizWindowId');
        if (windowId && windowId !== this.originalWindowId) {
            Livewire.dispatch('multipleWindows');
            window.close();
        } else {
            sessionStorage.setItem('quizWindowId', this.originalWindowId);
        }
    }
}
