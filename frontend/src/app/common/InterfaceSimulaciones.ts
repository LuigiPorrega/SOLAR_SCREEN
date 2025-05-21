export interface Simulacion {
  ID?: number;
  UsuarioID: number;
  CondicionLuz: string;
  EnergiaGenerada: number;
  Tiempo: number;
  Fecha: string | Date;
  CondicionesMeteorologicasID: number;
  FundaID: number;

  // Campo opcional que puede usarse para mostrar recomendaciones, calculado en frontend
  fundaRecomendada?: string;

  // Campo opcional para visualización amigable (ej: nombre del usuario o funda)
  nombre?: string;
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

export interface NuevaSimulacionDTO {
  CondicionLuz: string;
  EnergiaGenerada: number;
  Tiempo: number;
  Fecha: string;
  CondicionesMeteorologicasID: number;
  FundaID: number;
  UsuarioID: number;
}
