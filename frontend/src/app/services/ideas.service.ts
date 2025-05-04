import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';
import {ApiResponseIdeaDelete, ApiResponseIdeas, ApiResponseIdeaUpdateCreate, Idea} from '../common/InterfaceIdeas';

@Injectable({
  providedIn: 'root'
})
export class IdeasService {

  private readonly http: HttpClient = inject(HttpClient);

  constructor() {
  }

  //función para listar todas las Ideas paginadas
  getIdeas(page: number, pageLimit: number): Observable<ApiResponseIdeas> {
    return this.http.get<ApiResponseIdeas>(environment.baseURL + '/ideas?page=' + page + '&limit=' + pageLimit);
  };

  //función para listar una sola Idea
  getIdea(id: number ): Observable<Idea>{
    return this.http.get<Idea>(environment.baseURL +'/ideas/' +id);
  };

  //función para crear una nueva Idea
  addIdea(idea: Idea): Observable<ApiResponseIdeaUpdateCreate> {
    return this.http.post<ApiResponseIdeaUpdateCreate>(environment.baseURL +'/ideas' , idea);
  };

  //función para modificar una idea
  updateIdea(idea: Idea): Observable<ApiResponseIdeaUpdateCreate> {
    return this.http.put<ApiResponseIdeaUpdateCreate>(environment.baseURL +'/ideas/'+ idea.ID , idea);
  };

  //función para eliminar una idea
  deleteIdea(id: number ): Observable<ApiResponseIdeaDelete> {
    return this.http.delete<ApiResponseIdeaDelete>(environment.baseURL +'/ideas/' +id);
  };
}
