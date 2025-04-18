export interface CondicionMeteorologica {
  ID?: number;
  Fecha: string;
  LuzSolar: number;
  Temperatura: number;
  Humedad: number;
  Viento: number;
  UsuarioID?: number | null;
}

export interface ApiResponseCondicionesMeteorologicas {
  status: string;
  data: CondicionMeteorologica[];
  currentPage: number;
  perPage: number;
  totalItems: number;
  totalPages: number;
}

export interface ApiResponseCondicionCreateUpdate {
  status: string;
  message: string;
  data: CondicionMeteorologica;
}

export interface ApiResponseCondicionMeteorologicaDelete {
  status: string;
  message: string;
}
