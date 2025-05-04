import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {catchError, map, Observable, of, Subject, switchMap} from 'rxjs';
import {ModeloFunda} from '../common/InterfaceModelosFundas';
import {environment} from '../../environments/environment';


@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private readonly http: HttpClient = inject (HttpClient);
  private palabra : Subject<string> = new Subject<string>();


  private productSearched$ : Observable<ModeloFunda[]> = this.palabra.pipe(switchMap(res=>{
    return this.http.get<ModeloFunda[]>(environment.baseURL).pipe(
      map(products => products.filter(product=> product.Nombre.toLowerCase().includes(res.toLowerCase())))
    ).pipe(
      catchError(()=>of([]))
    );
  }))

  search(name:string){
    this.palabra.next(name);
  }

  start():Observable<ModeloFunda[]>{
    return this.productSearched$;
  }
}
