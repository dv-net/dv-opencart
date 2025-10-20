# DV.net Payment Gateway for OpenCart 🛒

DV.net is a reliable and convenient payment platform that allows you to accept a wide range of payments from customers worldwide.
With this official module for OpenCart, you can easily integrate DV.net into your online store, providing your customers with a fast,
secure, and modern way to pay for their orders.

## Key Features

1. Simple Integration: Quick installation and setup without the need for special technical knowledge.
1. Secure Payments: All transactions are processed on the secure DV.net side, ensuring the safety of your customers' data.
1. Automatic Status Updates: The module automatically updates the order status after a successful payment via webhooks.
1. Modern Payment Experience: Offer your customers a convenient and intuitive payment process.

## 📦 Installation

1. Download the module: Go to the official GitHub repository for the module. Navigate to the Releases section and download the latest version's archive, named `dv-opencart.ocmod.zip`.
1. Upload to OpenCart: In your site's admin panel, go to Extensions > Installer.
1. Click the Upload button and select the dv-opencart.ocmod.zip file you downloaded.
1. Install the extension: Go to Extensions > Extensions and select Payments from the dropdown list.
1. Find DV.net Gateway in the list and click the green Install button.\
1. Refresh modifications: Go to Extensions > Modifications and click the blue Refresh button in the upper right corner.

## ✅ Configuration

1. After installation, remain on the Payments page.
1. Find DV.net Gateway again and click the blue Edit button.
1. On the settings page, provide the following details:
   1. Merchant URL: Your merchant API URL at DV.net.
   1. API Key: Your API key for interacting with DV.net.
   1. API Secret: Your secret key for authenticating webhooks.\
   1. Completed Order Status: The status that will be assigned to an order after successful payment.
   1. Status: Set to Enabled to make the payment method available to customers.
   1. Sort Order: The display order for this payment method during checkout.
1. Click Save.
1. Add a webhook like `https://<your-host>/index.php?route=extension/payment/dv_gateway/callback`

Your store is now ready to accept payments through DV.net!