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
export interface ApiResponseCarritoList {
  status: string;
  data: CarritoItem[];
}

// Respuesta de POST y  PUT
export interface ApiResponseCarritoCreateUpdate {
  status: string;
  message: string;
  data: CarritoItem;
}

// Respuesta de DELETE
export interface ApiResponseCarritoDelete {
  status: string;
  message: string;
}
