jQuery(document).ready(function($) {
    if (eddOrders.orders.length > 0) {
        setInterval(function() {
            var randomOrder = eddOrders.orders[Math.floor(Math.random() * eddOrders.orders.length)];
            var productName = randomOrder.products[0].name;
            var productUrl = randomOrder.products[0].product_url;
            var productImage = randomOrder.products[0].image_url;
            var html = `
            <div class="notification-toast" style="display: none;">
                <div class="notification-wrapper">
                    <div class="notification-icon">
                        <img src="${productImage}" alt="Store Icon">
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">${randomOrder.customer} just purchased</p>
                        <a href="${productUrl}" class="notification-description">${productName}</a>
                        <p class="notification-time">${randomOrder.time_ago} by <span class="company-name">Rex Theme</span></p>
                    </div>
                </div>
                <button class="notification-close">
                    <svg width="12" height="12" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.50776 5.50008L10.7909 1.21668C11.0697 0.938066 11.0697 0.48758 10.7909 0.208963C10.5123 -0.0696543 10.0619 -0.0696543 9.78324 0.208963L5.49993 4.49236L1.21676 0.208963C0.938014 -0.0696543 0.487668 -0.0696543 0.209057 0.208963C-0.0696855 0.48758 -0.0696855 0.938066 0.209057 1.21668L4.49224 5.50008L0.209057 9.78348C-0.0696855 10.0621 -0.0696855 10.5126 0.209057 10.7912C0.347906 10.9302 0.530471 11 0.712906 11C0.895341 11 1.07778 10.9302 1.21676 10.7912L5.49993 6.5078L9.78324 10.7912C9.92222 10.9302 10.1047 11 10.2871 11C10.4695 11 10.652 10.9302 10.7909 10.7912C11.0697 10.5126 11.0697 10.0621 10.7909 9.78348L6.50776 5.50008Z" fill="#000000"/>
                    </svg>
                </button>
            </div>
            `;
            $('body').append(html);
            $('.notification-toast').last().fadeIn(400).delay(parseInt(eddOrders.rex_edd_toast_delay)).fadeOut(400, function() { $(this).remove(); });
        }, parseInt(eddOrders.rex_edd_toast_between_delay)); // Show a random toast every 3 seconds
    }

    jQuery(document).on('click','.notification-close',function(){
        jQuery(this).parent().fadeOut(400).remove()
    })
});
