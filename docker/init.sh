#!/bin/bash

# Generates a temporary SSL certificate
openssl genpkey -algorithm RSA -out ./ssl/default.pass.key -pass pass:x -pkeyopt rsa_keygen_bits:2048 -pkeyopt rsa_keygen_pubexp:3
openssl rsa -passin pass:x -in ./ssl/default.pass.key -out ./ssl/default.key
rm ./ssl/default.pass.key
openssl req -new -key ./ssl/default.key -out ./ssl/default.csr \
  -subj "/C=FR/ST=Haute-Garonne/L=Toulouse/O=Xtraball/OU=SysAdmin/CN=anders.xtraball.com"
openssl x509 -req -days 365 -in ./ssl/default.csr -signkey ./ssl/default.key -out ./ssl/default.crt