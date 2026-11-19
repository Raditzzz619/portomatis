(() => {
    const script = document.currentScript;
    if (!script) return;
    let timer;
    let stopped = false;
    const ping = async () => {
        clearTimeout(timer);
        if (stopped || document.visibilityState !== 'visible') return;
        try {
            const response = await fetch(script.dataset.heartbeat, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': script.dataset.token, Accept: 'application/json' },
            });
            if ([401, 403, 419].includes(response.status)) stopped = true;
        } catch { /* Retry when the page is connected again. */ }
        if (!stopped) timer = setTimeout(ping, 60000);
    };
    document.addEventListener('visibilitychange', ping);
    timer = setTimeout(ping, 60000);
})();
