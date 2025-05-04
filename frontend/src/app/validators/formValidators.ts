import {AbstractControl, ValidationErrors, ValidatorFn} from '@angular/forms';

export class FormValidators {
  //utilizo la validación para evitar espacios en blanco
  static notOnlyWhiteSpace(control: AbstractControl): ValidationErrors | null{
    if((control.value != null) && (control.value.trim().length == 0)){
      return { notOnlyWhiteSpace: true };
    }else{
      return null;
    }
  }

  //utilizo el REGEXR.COM (regla de extensión de nombre prohibido)
  static forbiddenName(name: RegExp) : ValidatorFn {
    return (control: AbstractControl) : ValidationErrors | null  => {
      const forbidden = name.test(control.value);
      return forbidden? {forbiddenName: {value: control.value}} : null;
    }
  }

  //utilizo el REGEXR.COM (regla de obligación de tipos de ficheros)
  static allowedData(files: RegExp) : ValidatorFn {
    return (control: AbstractControl) : ValidationErrors | null  => {
      const allowed = files.test(control.value);
      return allowed ? null : {allowedFiles : true}};
  }
}
