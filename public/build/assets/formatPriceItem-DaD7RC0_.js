function f(r){const e=`${r.price}₽`;return r.prefix==="from"?`от ${e}`:r.prefix==="to"?`до ${e}`:e}export{f};
