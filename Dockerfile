# Etapa de desarrollo
FROM node:20-alpine AS dev

WORKDIR /app

# Instala Angular CLI e Ionic CLI globalmente
RUN npm install -g @angular/cli @ionic/cli

# Copia package.json si existe
COPY package*.json ./

# Instala dependencias si existen
RUN [ -f package.json ] && npm install || echo "No package.json found. Will create Angular project at runtime."

# Copia el resto solo si existe
COPY . .

# Si no hay proyecto Angular creado, créalo
# Solo corre ng new si no existe angular.json Y .gitignore no existe
RUN [ ! -f angular.json ] && [ ! -f .gitignore ] && \
ng new . --skip-git --skip-install --style css --routing


# Instala dependencias del proyecto generado
RUN npm install

EXPOSE 4200

CMD ["ng", "serve", "--host", "0.0.0.0"]
