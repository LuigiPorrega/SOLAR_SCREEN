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

# Build de Angular con output explícito
RUN ng build --configuration production --output-path=/app/dist/frontend

# Etapa de producción con Nginx
FROM nginx:alpine

# Copia la build al directorio de Nginx
COPY --from=build /app/dist/frontend /usr/share/nginx/html

# Expone puerto 80
EXPOSE 80

# Arranca Nginx
CMD ["nginx", "-g", "daemon off;"]
