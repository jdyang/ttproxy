#include "global.h"
#include "session.h"
#include "udpsession.h"
#include "log.h"
#include "tinyxml.h"
#include <stdlib.h>

void stringReplace(string& name,string src,string dst)
{
	std::string::size_type startpos = 0;
	while (startpos!= std::string::npos)
	{
	    startpos = name.find(src.at(0));
	    if( startpos != std::string::npos )
	    {
	      name.replace(startpos,1,dst);
	    }
	}
}

void * work_thread(void * p)
{
  while(true)
  {
  	int cfd = 0;
  	g_fdqueue.Pop(cfd,true);
  	Session session(cfd);
  	session.process();
  }
}

void * udp_thread(void * p)
{
	TiXmlDocument *myDocument = new TiXmlDocument(g_cfg.c_str());
  myDocument->LoadFile();
  TiXmlElement *RootElement = myDocument->RootElement();
	TiXmlElement *udpElement  =  RootElement->FirstChildElement("udp");
  TiXmlElement *childElement = udpElement->FirstChildElement("ip");
  string ip = childElement->GetText();
  childElement = udpElement->FirstChildElement("port");
  string port = childElement->GetText();
  delete myDocument;
  LOG_INFO("init udp finish [ip:] %s [port:]%s",ip.c_str(),port.c_str());
	UdpSession session(ip,atoi(port.c_str()));
	session.process();
}
