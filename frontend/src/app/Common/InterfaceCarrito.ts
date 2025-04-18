// Un ítem del carrito
export interface CarritoItem {
  ID: number;
  UsuarioId: number;
  ModelosFundasId: number;
  Cantidad: number;
  Precio: number;
  Creado_en: string;
  NombreFunda?: string;
}

// Respuesta del GET /api/carrito
export interface ApiResponseCarrito {
  status: string;
  data: CarritoItem[];
}

// Respuesta de POST (add al carrito)
export interface ApiResponseCarritoAdd {
  status: string;
  message: string;
}

// Respuesta de PUT (actualizar cantidad)
export interface ApiResponseCarritoUpdate {
  status: string;
  message: string;
}

// Respuesta de DELETE
export interface ApiResponseCarritoDelete {
  status: string;
  message: string;
}
