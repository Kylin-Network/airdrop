# The Idea
The solution is based on the possibility of signing any message using the private key of an etherum account without interacting with the blockchain and use gas fees, just using Metamask or etherscan.io. The substrate address where the airdrop tokens will be included in a message that must be signed using the private key of the Etherum account (where original KYL tokens resides).

# User flow
![alt text](https://github.com/Kylin-Network/airdrop/blob/main/doc/user_flow.jpg?raw=true)

# Technical flow
![alt text](https://github.com/Kylin-Network/airdrop/blob/main/doc/tech_flow.jpg?raw=true)

# Technical details
The solutions chosen it respects the good practice principle - one app one container. 
Main containers:
- redis - for rate limiting and ddos protection
- mysql - db storage (initial database structure and data are found in ./backend/docker/mysql/dump.sql)
- php - php handler
- frontend - static file serving for the frontend signe
- phpmyadmin - easy DB administration for dev proposes
- nginx - backend engine written in php, based on a nginx image linked to the php container

# Run the frontend & backend
```bash
git clone https://github.com/Kylin-Network/airdrop
cd airdrop
docker-compose build && docker-compose up
```
If running on a local dev server with docker installed the components will be available on:
- phpMyAdmin - http://127.0.0.1:8085/
- frontend - http://127.0.0.1:8282/

# Distribution script
```bash
```
