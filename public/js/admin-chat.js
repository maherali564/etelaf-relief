// Admin Chat Panel - Custom JS
// Scroll messages to bottom on load
document.addEventListener('livewire:initialized', () => {
    Livewire.on('messages-loaded', () => {
        const msgArea = document.querySelector('[x-ref="adminMessages"]');
        if (msgArea) {
            msgArea.scrollTop = msgArea.scrollHeight;
        }
    });
});
