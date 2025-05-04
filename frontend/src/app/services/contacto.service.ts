import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import emailjs from '@emailjs/browser';



@Injectable({
  providedIn: 'root'
})
export class ContactoService {
  private readonly http: HttpClient= inject (HttpClient);

  private serviceID = 'service_0frzc9n';
  private templateID = 'template_z21xw0h';
  private publicKey = 'UdGoweI_lr8ETvuxk';


  constructor() {}

  enviarFormulario(data: any): Promise<any> {
    return emailjs.send(this.serviceID, this.templateID, data, this.publicKey);
  }
}
