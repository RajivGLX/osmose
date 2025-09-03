import { Admin } from "./admin.interface"
import { Patient } from "./patient.interface"

export interface User {
  id: number,
  administrator: Admin,
  patient: Patient,
  roles: Array<string>
  civilite: number,
  lastname: string,
  firstname: string,
  email: string,
  valid: boolean,
  create_at: Date,
  adminDialyzone: boolean,
}
