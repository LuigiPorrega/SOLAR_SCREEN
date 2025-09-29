# Etapa de build de Angular
FROM node:20-alpine AS build

WORKDIR /app

# Instala Angular CLI globalmente
RUN npm install -g @angular/cli

# Copia package.json y package-lock.json
COPY package*.json ./

# Instala dependencias
RUN npm install

# Copia el resto del proyecto
COPY . .

# Build de Angular en modo producción
RUN ng build --configuration production

# Etapa de producción con Nginx
FROM nginx:alpine

# Copia la build de Angular a Nginx
COPY --from=build /app/dist/frontend /usr/share/nginx/html

# Copia configuración de Nginx personalizada si la tienes
# COPY nginx.conf /etc/nginx/conf.d/default.conf

# Exponer puerto 80 para Nginx
EXPOSE 80

# Comando por defecto
CMD ["nginx", "-g", "daemon off;"]
