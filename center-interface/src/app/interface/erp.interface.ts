export interface Erp {

    id: number,
    type: number,
    etat: number,
    cle: string,
    reference_dossier: string
    nom: string
    beneficiaire: string
    adresse: string
    cp: string
    ville: string
    code_insee: string
    commentaire: string
    date_renouvellement: Date
    date_renouvellement_gratuit: Date
    date_maj_prefectorale: Date
    created_at: Date
    generated_at: Date
    statut: string
    commandeGenapi: {},
    statutPaiement: {},
    notifications: {},
    user: {}
    etude:{
        id:number
    }
}
