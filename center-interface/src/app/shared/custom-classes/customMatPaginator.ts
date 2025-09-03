import {MatPaginatorIntl} from "@angular/material/paginator";
import { Injectable } from "@angular/core";

@Injectable()
export class CustomMatPaginator extends MatPaginatorIntl {
    override itemsPerPageLabel="Éléments par page";
    override nextPageLabel="Page suivante";
    override previousPageLabel="Page précédente";
    override firstPageLabel="Première page";
    override lastPageLabel="Dernièr e page";

}
