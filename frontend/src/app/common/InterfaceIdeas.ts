export interface Idea {
  ID?: number;  // Puede no estar al crear
  UsuarioID: number;
  Titulo: string;
  Descripcion: string;
  FechaCreacion: string;
  UsuarioNombre?: string; // Solo presente si el backend lo incluye
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
