export interface ApiResponseModelosFundas {
  status: string;
  data: ModeloFunda[];
  currentPage: number;
  perPage: number;
  totalItems: number;
  totalPages: number;
}

export interface ApiResponseModeloFundaOne {
  status: string;
  data: {
    modeloFunda: ModeloFunda;
    proveedores: Proveedor[];
  };
}

export interface ModeloFunda {
  ID: number;
  Nombre: string;
  Tamaño: string;
  CapacidadCarga: number;
  Expansible: boolean;
  ImagenURL: string;
  Cantidad: number;
  Precio: number;
  TipoFunda: string;
  FechaCreacion: string;
}

export interface Proveedor {
  ID: number;
  Nombre: string;
  Pais: string;
  ContactoNombre: string;
  ContactoTelefono: string;
  ContactoEmail: string;
  SitioWeb: string | null;
  Direccion: string;
  Descripcion: string;
  FechaCreacion: string;
  Activo: boolean;
}

export interface ApiResponseModeloFundaUpdate {
  status: string;
  message: string;
  data: ModeloFundaConProveedores;
}

export interface ModeloFundaConProveedores extends ModeloFunda {
  ProveedorID: number[];
}

export interface ApiResponseModeloFundaCreate {
  status: string;
  message: string;
  data: {
    FechaCreacion: string;
  };
}

export interface ApiResponseDelete {
  status: string;
  message: string;
}

