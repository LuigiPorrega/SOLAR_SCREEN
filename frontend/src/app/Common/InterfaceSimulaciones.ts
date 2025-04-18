export interface Simulacion {
  ID?: number;
  UsuarioID: number;
  CondicionLuz: string;
  EnergiaGenerada: number;
  Tiempo: number;
  Fecha: string;
  CondicionesMeteorologicasID: number;
  FundaID: number;
}

export interface ApiResponseSimulaciones {
  status: string;
  data: Simulacion[];
  currentPage: number;
  perPage: number;
  totalItems: number;
  totalPages: number;
}

export interface ApiResponseSimulacionesCreateUpdate {
  status: string;
  message: string;
  data: Simulacion;
}

export interface ApiResponseSimulacionesDelete {
  status: string;
  message: string;
}
