# Etapa de build de Angular
FROM node:20-alpine AS build

WORKDIR /app

# Instala Angular CLI
RUN npm install -g @angular/cli

# Copia dependencias
COPY package*.json ./
RUN npm install

# Copia el resto del proyecto
COPY . .

# Build de Angular en producción (salida en /app/dist/frontend/browser)
RUN ng build --configuration production --output-path=/app/dist/frontend

# Etapa de producción con Nginx
FROM nginx:alpine

# Copia la build de Angular al directorio correcto de Nginx
COPY --from=build /app/dist/frontend/browser /usr/share/nginx/html

# Copia configuración personalizada de Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expone puerto 80
EXPOSE 80

# Arranca Nginx
CMD ["nginx", "-g", "daemon off;"]
