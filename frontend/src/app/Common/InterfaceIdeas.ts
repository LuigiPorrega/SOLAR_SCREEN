export interface Idea {
  ID?: string;  // El ID puede no estar presente si es una creación
  UsuarioID: string;
  Titulo: string;
  Descripcion: string;
  FechaCreacion: string;
}

export interface ApiResponseIdeas {
  status: string;
  data: Idea[];
  currentPage: number;
  perPage: number;
  totalItems: number;
  totalPages: number;
}

export interface ApiResponseIdeaUpdateCreate {
  status: string;
  message: string;
  data: Idea;
}

export interface ApiResponseIdeaDelete {
  status: string;
  message: string;
}



