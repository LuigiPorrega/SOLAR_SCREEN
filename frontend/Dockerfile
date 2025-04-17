# Etapa de desarrollo
FROM node:20-alpine AS dev

WORKDIR /app

# Instala Angular CLI e Ionic CLI globalmente
RUN npm install -g @angular/cli @ionic/cli

# Copia package.json y package-lock.json
COPY package*.json ./

# Instala dependencias
RUN npm install

# Copia el resto del proyecto
COPY . .

# Exponer el puerto donde se sirve Angular
EXPOSE 4200

# Comando por defecto: levantar el servidor Angular
CMD ["ng", "serve", "--host", "0.0.0.0"]
