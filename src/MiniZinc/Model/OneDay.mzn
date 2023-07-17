% Planno
% Complete a planning of one day

include "alldifferent.mzn";

% we use the first table to start (prefix 1)
int: NumberOfColumns1;
int: NumberOfColumns2;
int: NumberOfColumns3;

int: NumberOfRows1;
int: NumberOfRows2;
int: NumberOfRows3;

set of int: ColumnRange = 1..NumberOfColumns1;
set of int: RowRange = 1..NumberOfRows1;

set of int: Agents;

array[1..NumberOfRows1, 1..NumberOfColumns1] of var Agents: table;

% Ne pas placer un agent sur plusieurs postes en même temps
% = Tous différents sur une même colonne
constraint forall(j in ColumnRange)(
  alldifferent([ table[i,j] | i in RowRange]));

% Ne pas placer les agents à 12h et à 13h (sans repas)
% = Tous différents sur l'ensemble des cellules des colonnes 12h et 13h (ici : colonne 4 et 5)
constraint alldifferent([table[i, j] | i in RowRange, j in 4..5 ]);

% Ne pas placer le même agent plusieurs fois sur le même poste
% = Tous différents sur une même ligne
constraint forall(i in RowRange)(
  alldifferent([ table[i,j] | j in ColumnRange]));


constraint forall(i in RowRange, j in 1..3)(
  table[i,NumberOfColumns1] != Agents[j]);
  
constraint forall(i in RowRange, j in 4..5)(
  table[i,1] != Agents[j]);
  
solve satisfy;
