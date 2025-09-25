# ☀️ Solar Screen

**Solar Screen** es una plataforma web desarrollada en Angular y CodeIgniter que permite simular la carga energética de fundas inteligentes para móviles, basada en condiciones meteorológicas reales. El proyecto combina frontend moderno, backend API RESTful, y base de datos relacional, todo containerizado con Docker.

---

## 🚀 Funcionalidades principales

- 🔍 Búsqueda y carga de condiciones meteorológicas por ciudad (API externa)
- 🌤️ Gestión de condiciones meteorológicas personalizadas
- ⚡ Simulación de carga energética según luz solar y funda seleccionada
- 🧠 Recomendación automática de fundas compatibles
- 📦 Gestión de ideas, modelos de fundas y proveedores
- 📊 Exportación de resultados a PDF y CSV (gráfico incluido)
- 👤 Sistema de login y control de accesos por rol (usuario/admin)
- 🛒 Carrito de fundas (en desarrollo)


---

## 🧱 Stack tecnológico

- **Frontend**: Angular 17 (standalone components), Bootstrap 5, Chart.js
- **Backend**: CodeIgniter 4 (API RESTful)
- **Base de Datos**: MySQL, administrado con Adminer
- **Contenedores**: Docker + Docker Compose
- **Autenticación**: JWT tokens
- **Exportación**: jsPDF + html2canvas

---

## ⚙️ Instalación local

1. Asegúrate de que el puerto `8000` no esté ocupado (Portainer, por ejemplo).
2. Clona el repositorio:

```bash
git clonehttps://github.com/LuigiPorrega/SOLAR_SCREEN/solar_screen.git
cd solar-screen
```

3. Lanza la base de datos y backend:

```bash
cd PFC_Solar_Screen
docker-compose down
docker-compose build
docker-compose up -d
```

4. Accede a Adminer: [http://65.108.85.99:8080](http://65.108.85.99:8080)  
   - Usuario: `root`  
   - Contraseña: `solvam`  
   - Base de datos: `solar_screen`

5. El backend estará disponible en:  
   [http://65.108.85.99:8000/](http://65.108.85.99:8000/)

6.  El backend de la API RestFull estará disponible en:  
   [http://65.108.85.99:8000/api](http://65.108.85.99:8000/api)

7. El frontend estará disponible en:  
   [http://65.108.85.99:4200](http://65.108.85.99:4200)

## 🔐 Credenciales de prueba

**Administrador**  
Usuario: `luigip`  
Contraseña: `Solvam1234`

**Usuario estándar**  
Usuario: `luigi`  
Contraseña: `Solvam1234`

---

## 📡 API REST

Puedes ver todos los endpoints disponibles accediendo a:

```
http://65.108.85.99:8000/api
```

Ejemplo:

```http
GET /api/condicionesMeteorologicas
POST /api/simulaciones
GET /api/modelosFundas
```

Incluye control de acceso por rol:
- `GET`: Público
- `POST/PUT`: Solo usuario logueado (según autor)
- `DELETE`: Solo admin

---

## 📌 Conclusiones

Este proyecto me ha permitido integrar múltiples tecnologías (Angular, CodeIgniter, Docker, JWT, Chart.js) en una aplicación completa. Algunos retos enfrentados fueron:

- Integrar correctamente el flujo entre frontend y backend con seguridad.
- Generar gráficos dinámicos y exportarlos junto con los datos.
- Coordinar la autenticación y autorización entre sesiones Angular y PHP.
- Modularizar la lógica de simulación energética y recomendaciones.

Cada obstáculo me ha ayudado a aprender y consolidar buenas prácticas de desarrollo web fullstack.

---

## 🔮 Líneas de futuro

- 💾 Guardar condiciones meteorológicas personalizadas para simular sin usar la API externa.
- 📱 Añadir soporte para más dispositivos y fundas inteligentes.
- 🎨 Mejorar la interfaz visual con temas personalizados o modo oscuro.
- 🔔 Integrar notificaciones (Toasts, banners, etc.) en acciones críticas.
- 🌍 Implementar localización multilingüe (i18n).
- 🛒 Finalizar e integrar la lógica completa del carrito de fundas.
- 📱 Posible versión móvil (PWA o app nativa).

---

## 👨‍💻 Autor

**Luigi** – Estudiante de informática apasionado por el desarrollo fullstack, diseño UI moderno y soluciones tecnológicas eficientes.

[💼 https://www.linkedin.com/in/luigiporrega] | [🐙 https://github.com/LuigiPorrega] | [📧 luis.porrega@gmail.com]

---

## 📝 Licencia

Este proyecto está licenciado bajo la MIT License.
