
if (!window.wasaKreditMonthlyCostWidget) {
    var monthlyCostWidget = {
        showWasaKreditFinancingModal: function (guid, monthlyCost) {
            const es = document.querySelector(`.wasa-kredit-information-overlay[data-id='${guid}']`);
            es.setAttribute('style', 'display:block;');
            if (window.wasaKreditAnalyticsTracker) {
                window.wasaKreditAnalyticsTracker.trackEvent({
                    action: 'Read more',
                    category: 'Monthly Cost Widget',
                    label: 'Monthly Cost Widget 2.0',
                    value: monthlyCost
                });
                window.wasaKreditAnalyticsTracker.lastOpenModal = Math.floor(Date.now() / 1000);
            }
        },
        closeWasaKreditFinancingModal: function (e) {
            if (e.target === e.currentTarget) {
                const es = document.getElementsByClassName("wasa-kredit-information-overlay");
                for (let i = 0; i < es.length; i++) {
                    es[i].removeAttribute('style');
                }
                if (window.wasaKreditAnalyticsTracker) {
                    const overlay_open_in_seconds = Math.floor(Date.now() / 1000) - window.wasaKreditAnalyticsTracker.lastOpenModal;
                    window.wasaKreditAnalyticsTracker.trackEvent({
                        action: 'Closed',
                        category: 'Monthly Cost Widget',
                        label: 'Monthly Cost Widget 2.0',
                        value: overlay_open_in_seconds
                    });
                }
            }
        }
    };
    window.wasaKreditMonthlyCostWidget = monthlyCostWidget;
}
