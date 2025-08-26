async function checkCgfVersion(){

  let cgfVersion = localStorage.getItem("cgfVersion");
  cgfVersion = cgfVersion ? cgfVersion : 0;

  const data = {
    "cgfVersion": cgfVersion
  };
    
  const settings = {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
  };
  
  await fetch('/login/checkCgfVersion', settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      
      if(ret.success){
        
        if(ret.cgfVersion != cgfVersion){
          localStorage.setItem("cgfVersion", ret.cgfVersion);
        }else{
          console.log(`${ret.portalName} está atualizado: V${ret.cgfVersion}`);
        }        
      
      }
  
    }).catch(() => {
        
    });
}

checkCgfVersion();