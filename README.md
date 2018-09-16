# EasyTGBot
How to install EasyTGBot: open a chat with t.me/EasyTGBot, press start and "Genera Key", the bot generated a key, copy it,
open the index.php file and replace FPAM with the key. Now setup the webhook:
Open a browser and paste it, and replace the parameters
https://api.telegram.org/bot{TOKEN}/setwebhook?url=https://{DOMAIN}/{DIRECTORY}?fpam={KEY}%26token={TOKEN}
Do the request and start creating commands on commands.php


If you need help: https://t.me/EasyTGBotGroup
