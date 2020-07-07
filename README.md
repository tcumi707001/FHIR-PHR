# 實驗用憑證文件放於serverptx目錄中
# vendor文件為jwt.io官方涵式庫下載，對應PHP語言
  //https://jwt.io/
1. portal主要進行token核發，並將token傳遞給與resource網頁
2. resource 再收到token後儲存於cookie
3. resource網頁執行服務時，如有需要向FHIR server 存取資料，則進行token檢查，檢驗授權
