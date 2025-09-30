# Etapa build de Angular
FROM node:20-alpine AS build

WORKDIR /app

# Instala Angular CLI
RUN npm install -g @angular/cli

# Copia dependencias y proyecto
COPY package*.json ./
RUN npm install
COPY . .

# Build de producción
RUN ng build --configuration production --output-path=dist/frontend

# Etapa de producción con Nginx
FROM nginx:alpine

# Copia la build de Angular a Nginx
COPY --from=build /app/dist/frontend /usr/share/nginx/html

# Exponer puerto 80
EXPOSE 80

# Ejecutar Nginx
CMD ["nginx", "-g", "daemon off;"]
