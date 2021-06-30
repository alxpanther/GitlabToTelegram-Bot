# GitlabToTelegram-Bot
A bot that posts all commits and merge requests for the master branch in a Gitlab repository to a Telegram chat

## Setup
1. Start a chat with the account BotFather on Telegram
2. Send the `/newbot` command to the BotFather account and follow the process to create a bot and get its token
3. Add your newly created bot to the chat where you want the Gitlab commits and merge requests to be displayed
4. Visit https://api.telegram.org/botTOKEN/getUpdates (replace `TOKEN` with the token you got from the BotFather account in step 2) in your browser and search for the `chat_id` from the chat you added your bot to
5. Open *gitlab-to-telegram-bot.php* and replace `TELEGRAM_TOKEN` with the token you got from the BotFather account in step 2, replace `TELEGRAM_CHATID` with the id you got by visiting the website in step 4 and replace `GITLAB_TOKEN` a token of you invent
6. Save *gitlab-to-telegram-bot.php* and upload it to a publicly accessible part on your webserver that supports PHP
7. Open the settings of your Gitlab repository in your browser and navigate to the integrations page
8. Type the url to the index.php on your webserver in the *URL* field and the Gitlab token you invented in step 5 in the *Secret Token* field, check off *Push events* and *Merge Request events* and click on the *Add webhook* button
9. Test if everything works

## More Information
- [Telegram Bot API - Introduction](https://core.telegram.org/bots)
- [Telegram Bot API - Full Documentation](https://core.telegram.org/bots/api)
- [Gitlab Webhooks](https://docs.gitlab.com/ce/user/project/integrations/webhooks.html)

***
© Yannik Bloscheck - All rights reserved (edited by Alexander Fedorko)
