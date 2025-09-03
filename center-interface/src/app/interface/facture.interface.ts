export interface Facture {
    id: number,
    number: string,
    state: boolean,
    mean_of_payment: string,
    amount_excluding_taxes: number,
    amount_all_charges: number,
    created_at: Date,
    type: string
}

